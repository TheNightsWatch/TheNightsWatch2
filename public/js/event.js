$(function () {
    var absTime = $('#absolutetime');
    if (absTime.length) {
        var timestamp = absTime.val();
        var d = new Date();
        d.setTime(timestamp * 1000);

        var month = 1 + d.getMonth();
        if (month < 10) {
            month = '0' + month;
        }
        var date = d.getDate();
        if (date < 10) {
            date = '0' + date;
        }
        var date = d.getFullYear() + '-' + month + '-' + date;

        var hours = d.getHours();
        if (hours < 10) {
            hours = '0' + hours;
        }
        var minutes = d.getMinutes();
        if (minutes < 10) {
            minutes = '0' + minutes;
        }
        var time = hours + ':' + minutes;

        $('#date').val(date);
        $('#time').val(time);
    }
});
