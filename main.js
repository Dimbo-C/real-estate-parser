$(document).ready(function () {
    $('#forgot').leanModal();

    $('#reset').on('click', function (e) {
        e.preventDefault();
        var data = $('#forgot-form').serializeArray();
        console.log(data);
        $.ajax({
            type: "POST",
            url: 'forgot.php',
            data: data,
            success: function (response) {
                console.log(response);
                if (response == 'error') {
                    Materialize.toast('You write wrong email', 3000) // 4000 is the duration of the toast
                } else {
                    Materialize.toast('Message was sent to your email', 3000) // 4000 is the duration of the toast
                }
            }
        });
    });
});
