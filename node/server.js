var mysql = require('mysql'),
    sanitize = require('validator').sanitize,
    config = require('./config.js'),
    io = require('socket.io').listen(config.port),
    mysqlConnection = mysql.createConnection({
        host: config.mysql.server,
        user: config.mysql.username,
        database: config.mysql.database,
        password: config.mysql.password
    });

io.set('log level', 1);

var rooms = ['public', 'recruit', 'private', 'council'];

var messageLog = {};

var socketVariables = {};

function populateTheMessageLog() {
    for (var i in rooms) {
        var room = rooms[i];
        mysqlConnection.query('SELECT `chatroom`,`timestamp`,`message`,`user`.`username` AS `user` FROM `chatMessage` LEFT JOIN `user` ON(`user`.`id`=`chatMessage`.`user_id`) WHERE chatroom = ? ORDER BY chatMessage.timestamp DESC, chatMessage.id DESC LIMIT 20', [room], function (err, rows) {
            if (err) {
                console.error(err);
            }
            for (var j in rows) {
                var row = rows[j];
                addToMessageLog({ message: sanitize(row.message).escape(), user: row.user, time: row.timestamp.getTime(), room: row.chatroom }, row.chatroom);
            }
            if (i == rooms.length - 1) {
                emitMessageLogTo(io.sockets);
            }
        });
    }
}

function addToMessageLog(message, room) {
    if (messageLog[room] == undefined) {
        messageLog[room] = [];
    }
    if (messageLog[room].length >= 20) {
        messageLog[room].shift();
    }
    messageLog[room].push(message);
}

function emitMessageLogTo(to, rooms) {
    var tempLog = [];
    for (var i in rooms) {
        var messageLogRoom = messageLog[rooms[i]];
        for (var j in messageLogRoom) {
            tempLog.push(messageLogRoom[j]);
        }
    }
    to.emit('messages', tempLog);
}

function emitRoomViewersTo(to, rooms) {
    var message = {};
    for (var roomI in rooms) {
        var room = rooms[roomI];
        message[room] = [];
        var clients = io.sockets.clients(room);
        for (var clientI in clients) {
            var client = clients[clientI];
            var info = socketVariables[client.id];
            if (info.username != undefined) {
                message[room].push(info.username);
            }
        }
    }
    to.emit('members', message);
}

function mysqlStoreMessage(data) {
    var room = data.room;
    var user = data.userId;
    var message = data.message;
    mysqlConnection.query("INSERT INTO chatMessage (`user_id`,`chatroom`,`message`,`timestamp`) VALUES (?, ?, ?, CURRENT_TIMESTAMP)", [user, room, message], function (err, result) {
        if (err) {
            console.error(err);
            mysqlStoreMessage(data);
        }
    });
}

function updatePrivileges(socket) {
    var info = socketVariables[socket.id];
    mysqlConnection.query('SELECT rank, username FROM user WHERE id=?', [ info.userId ], function(err, rows) {
        if (err) {
            console.error(err);
        }
        if (rows.length < 1) {
            socketVariables[socket.id].rank = 0;
        } else {
            var row = rows[0];
            socketVariables[socket.id].rank = row.rank;
            var channelMap = {};
            for (var i in info.channels) {
                channelMap[info.channels[i]] = true;
            }

            var channelTests = [['recruit', 1], ['private', 2], ['council', 1000]];
            var activateChannels = [];
            var deactivateChannels = [];
            var channels = [];
            for (var i in channelTests) {
                var channelName = channelTests[i][0];
                var rankTest = channelTests[i][1];
                if (channelMap[channelName] && row.rank < rankTest) {
                    deactivateChannels.push(channelName);
                    io.sockets.in(channelName).emit('leave', [channelName, row.username]);
                    socket.leave(channelName);
                } else if (!channelMap[channelName] && row.rank >= rankTest) {
                    channels.push(channelName);
                    socket.join(channelName);
                    io.sockets.in(channelName).emit('join', [channelName, row.username]);
                    activateChannels.push(channelName);
                }
                if (row.rank >= rankTest) {
                    channels.push(channelName);
                }
            }
            socketVariables[socket.id].channels = channels;
            if (activateChannels.length) {
                socket.emit('activateChannels', activateChannels);
            }
            if (deactivateChannels.length) {
                socket.emit('deactivateChannels', deactivateChannels);
            }
        }
    });
}

populateTheMessageLog();
io.sockets.on('connection', function (socket) {
    socketVariables[socket.id] = {};
    emitMessageLogTo(socket, ['public']);
    emitRoomViewersTo(socket, ['public']);
    socket.join('public');
    socket.on('token', function (data) {
        mysqlConnection.query("SELECT user.username AS username, user.rank AS rank, user.id AS userId FROM chatToken LEFT JOIN user ON(chatToken.user_id=user.id) WHERE chatToken.token LIKE ? AND chatToken.expires > CURRENT_TIMESTAMP", [data], function (err, rows) {
            mysqlConnection.query("DELETE FROM `chatToken` WHERE `token` = ? OR `expires` < CURRENT_TIMESTAMP", [data], function (err, result) {
                if (err) {
                    console.error(err);
                }
            });
            if (err) {
                console.error(err);
            }
            if (rows.length) {
                var row = rows[0];
                if (!socketVariables[socket.id]) {
                    socketVariables[socket.id] = {};
                }
                socketVariables[socket.id].username = row.username;
                socketVariables[socket.id].rank = row.rank;
                socketVariables[socket.id].userId = row.userId;
                // Subscribe to Channels
                var channels = [];
                io.sockets.in('public').emit('join', ['public', row.username]);
                if (row.rank >= 1) { // recruit+
                    channels.push('recruit');
                    socket.join('recruit');
                    io.sockets.in('recruit').emit('join', ['recruit', row.username]);
                }
                if (row.rank >= 2) { // private+
                    channels.push('private');
                    socket.join('private');
                    io.sockets.in('recruit').emit('join', ['private', row.username]);
                }
                if (row.rank >= 1000) { // lieutenant+
                    channels.push('council');
                    socket.join('council');
                    io.sockets.in('recruit').emit('join', ['council', row.username]);
                }
                socketVariables[socket.id].channels = channels;
                emitMessageLogTo(socket, channels);
                socket.emit('verified');
                emitRoomViewersTo(socket, channels);
            } else {
                console.error('Bad Token ', data);
            }
        });
    });
    socket.on('disconnect', function (data) {
        // tell the rooms the user was in that they are no longer there
        var rooms = io.sockets.manager.roomClients[socket.id];
        for (var room in rooms) {
            if (room.substr(0, 1) == '/') {
                // TODO fill in with user.
                if (socketVariables[socket.id].username) {
                    io.sockets.in(room.substr(1)).emit('leave', [room.substr(1), socketVariables[socket.id].username]);
                }
            }
        }
        delete socketVariables[socket.id];
    });
    socket.on('message', function (data) {
        var info = socketVariables[socket.id];
        if (!info.username) {
            return;
        }
        var room = data.room.toLowerCase();
        if ((room == 'recruit' && info.rank < 1) ||
            (room == 'private' && info.rank < 2) ||
            (room == 'council' && info.rank < 1000)
            ) {
            return;
        }
        var message = { room: data.room, user: socketVariables[socket.id].username, time: (new Date).getTime(), message: sanitize(data.message).escape() };
        data.userId = info.userId;
        mysqlStoreMessage(data);
        updatePrivileges(socket);
        addToMessageLog(message, data.room);
        io.sockets.in(data.room).emit('messages', [ message ]);
    });
});
