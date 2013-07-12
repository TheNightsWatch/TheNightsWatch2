var mysql = require('mysql'),
    config = require('./config.js'),
    io = require('socket.io').listen(config.port),
    mysqlConnection = mysql.createConnection({
        host: config.server,
        user: config.username,
        database: config.database,
        password: config.password
    });

io.sockets.on('connection', function (socket) {
    socket.emit('news', { hello: 'world'});
});
