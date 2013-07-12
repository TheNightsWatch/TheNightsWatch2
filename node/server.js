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
    emitMessageLogTo(socket, ['public']);
    socket.on('token', function (data) {

    });
    socket.on('disconnect', function (data) {
        // tell the rooms the user was in that they are no longer there
        var rooms = io.sockets.manager.roomClients[socket.id];
        for (var i in rooms) {
            var room = rooms[i];
            if (room.substr(0, 1) == '/') {
                // TODO fill in with user.
                io.sockets.in(room.substr(1)).emit('leave', { user: 'TODO' });
            }
        }
    });
    socket.on('message', function (data) {
        var message = { room: data.room, user: 'Navarr', time: (new Date).getTime(), message: sanitize(data.message).escape() };
        mysqlStoreMessage(data);
        addToMessageLog(message, data.room);
        io.sockets.in(data.room).emit('messages', [ message ]);
    });
});
