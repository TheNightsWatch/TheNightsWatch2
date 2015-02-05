$(document).ready(function () {
    window.showNotificationsFlag = false;

    var titleTag = $('title');
    titleTag.data('original', titleTag.text());

    window.titlebar = {
        "counter": 0,
        "newFlag": false,
        "flashingFlag": false,
        "flashingMsgFlag": false,
        "flashingMsg": "",
        "showingFlashMsg": false,

        "lastUpdate":
        {
            "counter": 0,
            "newFlag": false,
            "title": ""
        },
        "update": function(counter,newFlag) { window.titlebar.updateTitlebar(counter,newFlag); },
        "updateTitlebar": function(counter,newFlag)
        {
            if(counter != undefined && counter != null) { window.titlebar.counter = counter; }
            if(newFlag != undefined && newFlag != null) { window.titlebar.newFlag = newFlag; }
            if(document.title == window.titlebar.lastUpdate.title && window.titlebar.counter == window.titlebar.lastUpdate.counter && window.titlebar.newFlag == window.titlebar.lastUpdate.newFlag) { return false; }
            var newTitle = "";
            if(window.titlebar.newFlag)
                newTitle = "(*) ";
            if(window.titlebar.counter > 0)
                newTitle = newTitle + "(" + window.titlebar.counter + ") ";
            newTitle = newTitle + window.titlebar.getOriginal();

            document.title = newTitle;
            window.titlebar.showingFlashMsg = false;
            window.titlebar.lastUpdate = {"counter":window.titlebar.counter,"newFlag":window.titlebar.newFlag,"title":newTitle};
            console.log("New Title: " + newTitle);
        },
        "flashMessage": function(message)
        {
            if(message == undefined || message == null || message == false)
            {
                window.titlebar.flashingMsgFlag = false;
                window.titlebar.showingFlashMsg = false;
                clearInterval(window.titlebar.flashMsgInterval);
                window.titlebar.updateTitlebar();
            } else {
                window.titlebar.flashMessage(false); // Cleanup
                window.titlebar.flashingMsgFlag = true;
                window.titlebar.flashMsg = message;
                window.titlebar.flashMsgInterval = setInterval(function()
                {
                    console.log("Running Interval");
                    if(window.titlebar.showingFlashMsg)
                    {
                        console.log("Showing It, so moving to not showing it.");
                        window.titlebar.update();
                        window.titlebar.showingFlashMsg = false;
                    }
                    else
                    {
                        console.log("Not Showing It, so moving to showing it.");
                        document.title = window.titlebar.flashMsg;
                        window.titlebar.showingFlashMsg = true;
                    }
                },1000);
            }
        },
        "flash": function(truefalse)
        {
            if(truefalse == undefined) { truefalse = !(window.titlebar.flashingFlag); }
            if(truefalse)
            {
                window.titlebar.flashingFlag = true;
                window.titlebar.flashInterval = setInterval(function()
                {
                    window.titlebar.updateTitlebar(null,!(window.titlebar.newFlag));
                },1000);
            }
            else
            {
                window.titlebar.flashingFlag = false;
                clearInterval(window.titlebar.flashInterval);
                window.titlebar.updateTitlebar(null,false);
            }
        },
        "getOriginal": function()
        {
            return $('title').data('original');
        }
    };

    $([window, document]).on('focus', function() {
        window.titlebar.flashMessage(false);
        window.showNotificationsFlag = false;
    });

    $([window, document]).on('blur', function() {
        window.showNotificationsFlag = true;
    });

    window.showNotification = function() {
        if (!window.showNotificationsFlag) return;
        window.titlebar.flashMessage("New Chat Message!");
    };

    var $chatMessage = $('#chat-message');
    var $chatNav = $('#chat-nav');

    var currentRoom;

    var isActive = true;

    var initialLoad = true;

    var initialJoin = true;

    // make messageOverflowCache PER ROOM
    var messageOverflowCache = {};

    $(window).on('focus', function (e) {
        isActive = true;
        // clear notifications
    });
    $(window).on('blur', function (e) {
        isActive = false;
    });

    var afterChatHasLoaded = function () {
        // Remove the Loading Stuff
        $('#chat-message-container').removeClass('loading');

        $
            .ajax({
                url: '/chat/token',
                type: 'GET',
                dataType: 'json'
            })
            .success(function (data) {
                socket.emit('token', data.token);
            });

        // Navigation Handling
        $chatNav.on('click', 'li[data-room]', function (e) {
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
            $('.chat-viewers').hide();
            var newRoom = $('.chat-messages.' + currentRoom);
            $('.chat-viewers.' + currentRoom).show();
            newRoom.show();
            if ($this.data('atBottom') || $this.data('atBottom') == undefined) {
                scrollToBottom($chatContainer);
            } else {
                $chatContainer.prop('scrollTop', $this.data('scrollTop'));
            }
            $chatMessage.find('input[type=text]').focus();
        });
        if (initialJoin) {
            $chatNav.find('.public').trigger('click');
        }

        // Determine if user can post
        if ($chatMessage.data('hasidentity')) {
            $chatMessage.find('input[type=text]').attr('placeholder', 'Verifying Identity...');
            $chatMessage.fadeIn();
        }
    };

    // Auto Focus
    $chatMessage.find('input[type=text]').focus();

    // Handle Receipt of Messages
    var atBottomOfScrollList = function (selector) {
        var $obj = $(selector);
        var height = $obj.height();
        var scrollHeight = $obj.prop('scrollHeight');
        var margins = parseInt($obj.css('margin-top'), 10) + parseInt($obj.css('margin-bottom'));
        return scrollHeight - height - margins <= $obj.prop('scrollTop');
    };
    var scrollToBottom = function (selector) {
        var $obj = $(selector);
        $obj.prop({scrollTop: $obj.prop('scrollHeight')});
    };
    var socket = io.connect('/', { secure: true }).on('connect', afterChatHasLoaded);
    var addUserToRoom = function (room, username) {
        var $li = $('#viewer-template').clone();
        $li.attr('id', 'viewer-' + room + '-' + username);
        var html = $li.html();
        html = html.replace(/{link}/g, '/user/' + username);
        html = html.replace(/{user}/g, username);
        $li.html(html);
        $li.find('img').attr('src', 'https://crafatar.com/avatars/' + username + '?size=16');
        $('.chat-viewers.' + room).append($li);
    };

    socket.on('messages', function (messages) {
        var shouldScroll = initialLoad || atBottomOfScrollList('#chat-message-container');
        var shouldMarkUnread = !initialLoad;
        if (!$chatMessage.data('hasidentity')) {
            initialLoad = false;
        }
        for (var i = 0, l = messages.length; i < l; ++i) {
            var data = messages[i];

            if (!messageOverflowCache.hasOwnProperty(data.room)) {
                messageOverflowCache[data.room] = [];
            }

            var uniqueString = data.time + "|" + data.user + "|" + data.room + "|" + data.message;
            var cont = false;
            var m = messageOverflowCache[data.room].length;
            for(var j = 0;j < m;++j) {
                if (uniqueString == messageOverflowCache[data.room][j]) {
                    cont = true;
                    break;
                }
            }
            if (cont) {
                continue;
            } else {
                messageOverflowCache[data.room].push(uniqueString);
                if (messageOverflowCache[data.room].length >= 20) {
                    messageOverflowCache[data.room].unshift();
                }
            }

            var $li = $('#chat-message-template').clone();
            $li.attr('id', '');
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
            html = html.replace(/{link}/g, '/user/' + data.user);
            $li.html(html).show();

            var tableClass = '.' + data.room;

            if (shouldMarkUnread) {
                var $nav = $chatNav.find(tableClass);
                if (!$nav.hasClass('active')) {
                    $nav.addClass('unread');
                }
            }

            $('.chat-messages' + tableClass).find('ol').append($li);

            window.showNotification();
        }
        if (shouldScroll) {
            scrollToBottom('#chat-message-container');
        }
    });
    socket.on('members', function (rooms) {
        for (var room in rooms) {
            $('.chat-viewers.' + room).html('');
            for (var i in rooms[room]) {
                var viewer = rooms[room][i];
                addUserToRoom(room, viewer);
            }
        }
    });
    socket.on('join', function (data) {
        var room = data[0];
        var user = data[1];
        addUserToRoom(room, user);
    });
    socket.on('leave', function (data) {
        var room = data[0];
        var user = data[1];
        $('#viewer-' + room + '-' + user).remove();
    });
    socket.on('verified', function() {
        initialLoad = false;
        $chatMessage.find('input').attr('disabled', false);
        $chatMessage.find('input[type=text]').attr('placeholder', 'Type a Message...').focus();
    });
    socket.on('disconnect', function() {
        $chatMessage.find('input').attr('disabled', true);
        $chatMessage.find('input[type=text]').attr('placeholder', 'Attempt to Connect to Server...');
    });
    socket.on('activateChannels', function(channels) {
        for (var i in channels) {
            var channel = channels[i];
            var $channelLi = $chatNav.find('.' + channel);
            $channelLi.removeClass('hidden').hide().fadeIn().data('atBottom', true);
        }
    });
    socket.on('deactivateChannels', function(channels) {
        for (var i in channels) {
            var channel = channels[i];
            var $channelLi = $chatNav.find('.' + channel);
            if ($channelLi.hasClass('active')) {
                $chatNav.find('.public').trigger('click');
            }
            $channelLi.fadeOut();
        }
    });
    socket.on('defaultChannel', function(channel) {
        if (initialJoin) {
            $chatNav.find('li.' + channel).trigger('click');
            initialJoin = false;
        }
    });

    // Handle Sending a Chat Message
    $chatMessage.on('submit', function (e) {
        e.preventDefault();
        var $this = $(this), $text = $this.find('input[type=text]');
        if ($text.val().trim() == '') {
            return;
        }
        ga('send', 'event', 'chat', 'message', currentRoom, $text.length);
        socket.emit('message', { room: currentRoom, message: $text.val() });
        $text.val('');
    });
});
