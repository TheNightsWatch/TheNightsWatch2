var mysql = require('mysql'),
    config = require('./config.js'),
    io = require('socket.io').listen(config.port),
    mysqlConnection = mysql.createConnection({
        host: config.server,
        user: config.username,
        database: config.database,
        password: config.password
    });

var rooms = ['public','recruit','private','council'];

var messageLog = {};

function populateTheMessageLog()
{

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

io.sockets.on('connection', function (socket) {
    var tempLog = [];
    for(var i in messageLog) {
        var messageLogRoom = messageLog[i];
        for(var j in messageLogRoom) {
            tempLog.push(messageLogRoom[j]);
        };
    };
    socket.emit('messages', tempLog);
    socket.on('message', function (data) {
        var message = { room: data.room, user: 'Navarr', time: (new Date).getTime(), message: data.message };
        addToMessageLog(message, data.room);
        io.sockets.emit('messages', [ message ]);
    });
});
