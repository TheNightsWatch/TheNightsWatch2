$(document).ready(function () {
    var $chatMessage = $('#chat-message');
    var $chatNav = $('#chat-nav');

    var currentRoom;

    var afterChatHasLoaded = function()
    {
        // Remove the Loading Stuff
        $('#chat-message-container').removeClass('loading');

        // Navigation Handling
        $chatNav.on('click', 'li', function(e) {
            e.preventDefault();

            var $chatContainer = $('#chat-message-container');

            // Mark if the active li is at the bottom of scroll
            var $active = $chatNav.find('li.active');
            var atBottom = atBottomOfScrollList('#chat-message-container');
            if (atBottom) {
                $active.data('atBottom', true);
            } else {
                $active.data('atBottom', false);
                $active.data('scrollTop', $chatContainer.prop('scrollTop'));
            }

            var $this = $(this);
            $chatNav.find('li').removeClass('active');
            $this.addClass('active').removeClass('unread');

            currentRoom = $this.data('room');
            $('.chat-messages').hide();
            var newRoom = $('.chat-messages.' + currentRoom);
            newRoom.show();
            if ($this.data('atBottom') || $this.data('atBottom') == undefined) {
                scrollToBottom($chatContainer);
            } else {
                $chatContainer.prop('scrollTop', $this.data('scrollTop'));
            }
        });
        $chatNav.find('.public').trigger('click');
    };

    // Auto Focus
    $chatMessage.find('input[type=text]').focus();

    // Handle Receipt of Messages
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
    var socket = io.connect('/').on('connect', afterChatHasLoaded);
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

            var tableClass = '.' + data.room;

            var $nav = $chatNav.find(tableClass);
            if (!$nav.hasClass('active')) {
                $nav.addClass('unread');
            }

            $('.chat-messages' + tableClass).find('ol').append($li);
        }
        if (shouldScroll) {
            scrollToBottom('#chat-message-container');
        }
    });

    // Handle Sending a Chat Message
    $chatMessage.on('submit', function (e) {
        console.log('submit');
        e.preventDefault();
        var $this = $(this), $text = $this.find('input[type=text]');
        if ($text.val().trim() == '') {
            console.log('bad value');
            return;
        }
        console.log('message', $text.val());
        socket.emit('message', { room: currentRoom, message: $text.val() });
        $text.val('');
    });
});
