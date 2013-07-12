var mysql = require('mysql'),
    config = require('./config.js'),
    io = require('socket.io').listen(config.port),
    mysqlConnection = mysql.createConnection({
        host: config.server,
        user: config.username,
        database: config.database,
        password: config.password
    });

var messageLog = [];

var addToMessageLog = function(message) {
    if (messageLog.length >= 20) {
        messageLog.shift();
    }
    messageLog.push(message);
};

io.sockets.on('connection', function (socket) {
    socket.emit('messages', messageLog);
    socket.on('message', function (data) {
        var message = { user: 'Navarr', time: (new Date).getTime(), message: data };
        addToMessageLog(message);
        io.sockets.emit('messages', [ message ]);
    });
});
