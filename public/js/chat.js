$(document).ready(function () {
    $('#chat-message').find('input[type=text]').focus();
    var atBottomOfScrollList = function(selector) {
        var $obj = $(selector);
        var height = $obj.height();
        var scrollHeight = $obj.prop('scrollHeight');
        var margins = parseInt($obj.css('margin-top'), 10) + parseInt($obj.css('margin-bottom'));
        return scrollHeight - height - margins <= $obj.prop('scrollTop');
    };
    var scrollToBottom = function(selector) {
        var $obj = $(selector);
        $obj.prop({scrollTop: $obj.prop('scrollHeight')});
    };
    var socket = io.connect('/');
    var initialLoad = true;
    socket.on('messages', function (messages) {
        var shouldScroll = initialLoad || atBottomOfScrollList('#chat-message-container');
        initialLoad = false;
        for (var i = 0, l = messages.length; i < l; ++i) {
            data = messages[i];

            var $li = $('#chat-message-template').clone();
            var html = $li.html();
            var date = new Date();
            date.setTime(data.time);
            var hours = date.getHours();
            if (hours < 10) {
                hours = '0' + hours;
            }
            var minutes = date.getMinutes();
            if (minutes < 10) {
                minutes = '0' + minutes;
            }
            var stamp = hours + ':' + minutes;
            html = html.replace(/{user}/g, data.user);
            html = html.replace(/{time}/g, stamp);
            html = html.replace(/{message}/g, data.message);
            $li.html(html).show();

            $('#chat-messages').find('ol').append($li);
        }
        if (shouldScroll) {
            scrollToBottom('#chat-message-container');
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
        $text.val('');
    });
});
