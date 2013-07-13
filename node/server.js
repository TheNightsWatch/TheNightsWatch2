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

var rooms = ['public', 'recruit', 'private', 'council'];

var messageLog = {};

var socketVariables = {};

function populateTheMessageLog() {
    for (var i in rooms) {
        var room = rooms[i];
        mysqlConnection.query('SELECT `chatroom`,`timestamp`,`message`,`user`.`username` AS `user` FROM `chatMessage` LEFT JOIN `user` ON(`user`.`id`=`chatMessage`.`user_id`) WHERE chatroom = ? ORDER BY chatMessage.id DESC LIMIT 20', [room], function (err, rows) {
            if (err) {
                console.error(err);
            }
            for (var j in rows) {
                var row = rows[j];
                addToMessageLog({ message: sanitize(row.message).escape(), user: row.user, time: row.timestamp.getTime(), room: row.chatroom }, row.chatroom);
            }
            if(i == rooms.length-1) {
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

function mysqlStoreMessage(data) {
    var room = data.room;
    var user = 3;
    var message = data.message;
    mysqlConnection.query("INSERT INTO chatMessage (`user_id`,`chatroom`,`message`,`timestamp`) VALUES (?, ?, ?, CURRENT_TIMESTAMP)", [user, room, message], function(err, result) {
        if (err) {
            console.error(err);
            mysqlStoreMessage(data);
        }
    });
}

populateTheMessageLog();
io.sockets.on('connection', function (socket) {
    socketVariables[socket.id] = {};
    emitMessageLogTo(socket, ['public']);
    socket.join('public');
    socket.on('token', function (data) {
        mysqlConnection.query("SELECT user.username AS username, user.rank AS rank FROM chatToken LEFT JOIN user ON(chatToken.user_id=user.id) WHERE chatToken.token LIKE ? AND chatToken.expires > CURRENT_TIMESTAMP", [data], function(err, rows) {
            mysqlConnection.query("DELETE FROM `chatToken` WHERE `token` = ? OR `expires` < CURRENT_TIMESTAMP", [data], function(err, result) {
                if (err) {
                    console.error(err);
                }
            });
            if (err) {
                console.error(err);
            }
            if (rows.length) {
                var row = rows[0];
                socketVariables[socket.id].username = row.username;
                socketVariables[socket.id].rank = row.rank;
                // Subscribe to Channels
                var channels = [];
                if (row.rank >= 1) { // recruit+
                    channels.push('recruit');
                    socket.join('recruit');
                }
                if (row.rank >= 2) { // private+
                    channels.push('private');
                    socket.join('private');
                }
                if (row.rank >= 1000) { // lieutenant+
                    channels.push('council');
                    socket.join('council');
                }
                emitMessageLogTo(socket, channels);
            } else {
                console.log('Bad Token ', data);
            }
        });
    });
    socket.on('disconnect', function (data) {
        // tell the rooms the user was in that they are no longer there
        var rooms = io.sockets.manager.roomClients[socket.id];
        console.log(rooms);
        for (var i in rooms) {
            var room = i;
            if (room.substr(0, 1) == '/') {
                // TODO fill in with user.
                io.sockets.in(room.substr(1)).emit('leave', { user: 'TODO' });
            }
        }
        delete socketVariables[socket.id];
    });
    socket.on('message', function (data) {
        var message = { room: data.room, user: 'Navarr', time: (new Date).getTime(), message: sanitize(data.message).escape() };
        mysqlStoreMessage(data);
        addToMessageLog(message, data.room);
        io.sockets.in(data.room).emit('messages', [ message ]);
    });
});
