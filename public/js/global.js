$(function () {
    $('.datepicker').datepicker({format: 'yyyy-mm-dd', autoclose: true});

    $('.dateformat').each(function () {
        var $this = $(this);
        if ($this.data('unix') != undefined && $this.data('format') != undefined) {
            $this.text(date($this.data('format'), parseInt($this.data('unix'), 10)));
        }
    });

    var jsOffset = (new Date).getTimezoneOffset() * 60;
    $('.jsoffset').val(jsOffset);
});
