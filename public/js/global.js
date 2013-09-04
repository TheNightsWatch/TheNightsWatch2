$(function() {
    $('.datepicker').datepicker({startDate:'today', format:'d MM yyyy', autoclose: true});

    $('time').each(function() {
        var $this = $(this);
        if ($this.data('unix') != undefined && $this.data('format') != undefined) {
            $this.text(date($this.data('format'), parseInt($this.data('unix'), 10)));
        }
    });
});
