$(document).ready(function () {
    var socket = io.connect('/');
    socket.on('messages', function (messages) {
        for (var i = 0, l = messages.length; i < l; ++i) {
            data = messages[i];

            var $li = $('#chat-message-template').clone();
            var html = $li.html();
            html = html.replace(/{user}/g, data.user);
            html = html.replace(/{time}/g, data.time);
            html = html.replace(/{message}/g, data.message);
            $li.html(html).show();

            $('#chat-messages').find('ol').append($li);
        }
    });
    $('#chat-message').on('submit', function (e) {
        console.log('submit');
        e.preventDefault();
        var $this = $(this), $text = $this.find('input[type=text]');
        if ($text.val().trim() == '') {
            console.log('bad value');
            return;
        }
        console.log('message', $text.val());
        socket.emit('message', $text.val());
    });
});
