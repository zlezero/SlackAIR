$(function() {
    $('#password_form_password_first, #password_form_password_second').on('change', function(e) {
        if ($('#password_form_password_first').val() != $('#password_form_password_second').val()) {
            $('#password_form_password_first')[0].setCustomValidity("Les deux mots de passe doivent correspondre");
        } else {
            $('#password_form_password_first')[0].setCustomValidity("");
        }
    });
})

