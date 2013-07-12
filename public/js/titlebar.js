function TitleBar() {
    this.original = document.getElementsByTagName('title')[0].innerHTML;
    this.counter = 0;
    this.newFlag = false;
    this.flashingFlag = false;
    this.flashingMsgFlag = false;
    this.flashingMsg = "";
    this.showingFlashMsg = false;
    this.clearFlashTimeout = false;
    this.lastUpdate = {
        counter : 0,
        newFlag : false,
        title : ''
    };
    this.focused = true;
    this.onlyFlashWhenNotFocused = true;
    var _this = this;

    $(document).ready(function() {
        $(window).on('focus', function() {
            _this.focused = true;
            if (_this.onlyFlashWhenNotFocused) {
                _this.flashMessage(false, _this);
            }
        });
        $(window).on('blur', function() {
            _this.focused = false;
        });
    });
}
TitleBar.prototype.update = function(counter, newFlag, _this) {
    if (_this === undefined)
        var _this = this;
    _this.updateTitlebar(counter, newFlag, _this);
};
TitleBar.prototype.updateTitlebar = function(counter, newFlag, _this) {
    if (_this === undefined)
        var _this = this;
    if (counter !== undefined && counter !== null)
        _this.counter = counter;
    if (newFlag !== undefined && newFlag !== null)
        _this.newFlag = newFlag;
    if (document.title == _this.lastUpdate.title
        && _this.counter == _this.lastUpdate.counter
        && _this.newFlag == _this.lastUpdate.newFlag)
        return false;
    var newTitle = "";
    if (_this.newFlag)
        newTitle = '(*) ';
    if (_this.counter > 0)
        newTitle = newTitle + "(" + _this.counter + ") ";
    newTitle = newTitle + _this.getOriginal(_this);

    document.title = newTitle;
    _this.showingFlashMsg = false;
    _this.lastUpdate = {
        counter : _this.counter,
        newFlag : _this.newFlag,
        title : _this.title
    };
};
TitleBar.prototype.flashMessage = function(message, _this) {
    if (_this === undefined)
        var _this = this;
    if (message === undefined || message == null || message == false) {
        _this.flashingMsgFlag = false;
        _this.showingFlashMsg = false;
        clearInterval(_this.flashMsgInterval);
        // _this.clearFlashTimeout = setTimeout(function() { _this.flashMessage(message, _this); }, 1000);
        _this.updateTitlebar(undefined, undefined, _this);
    } else {
        if (_this.onlyFlashWhenNotFocused && _this.focused)
            return false;
        /*
        if (_this.clearFlashTimeout) {
            clearTimeout(_this.clearFlashTimeout);
        }
        */
        _this.flashMessage(false);
        _this.flashingMsgFlag = true;
        _this.flashMsg = message;
        _this.flashMsgInterval = setInterval(function() {
            if (_this.showingFlashMsg) {
                _this.update(undefined, undefined, _this);
                _this.showingFlashMsg = false;
            } else {
                document.title = _this.flashMsg;
                _this.showingFlashMsg = true;
            }
        }, 1000);
    }
};
TitleBar.prototype.flash = function(truefalse, _this) {
    if (_this === undefined)
        var _this = this;
    if (_this.onlyFlashWhenNotFocused && _this.focused)
        return false;
    if (truefalse == undefined) {
        truefalse = !(_this.flashingFlag);
    }
    if (truefalse) {
        _this.flashingFlag = true;
        _this.flashInterval = setInterval(function() {
            _this.updateTitlebar(null, !(_this.newFlag), _this);
        }, 1000);
    } else {
        _this.flashingFlag = false;
        clearInterval(_this.flashInterval);
        _this.updateTitlebar(null, false, _this);
    }
};
TitleBar.prototype.getOriginal = function(_this) {
    if (_this === undefined)
        var _this = this;
    return _this.original;
};
