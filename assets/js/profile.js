import * as modals from './modals';

$(function() {
    
    $('#user-profile-modal').on('show.bs.modal', function (e) {

        $("#user-update-form").on('submit', function (e) {

            e.preventDefault();

            $.post({
                url: '/api/user/setInfos',
                data: $("#user-update-form").serialize(),
                success: function(data) {

                    if (data.statut == "ok") {
                        $("#profile-user-pseudo").text(data["user"]["pseudo"]);
                        $("#sidebar-user-pseudo").text(data["user"]["pseudo"]);
                        $("#sidebar-user-pseudo").append('  <a href="#"><i class="fa fa-power-off"></i></a>');
                        $("#profile-user-age").text(data["user"]["age"]);
                        $("#profile-user-profession").text(data["user"]["profession"]);
                        $("#profile-user-department").text(data["user"]["departement"]["nom"]);
                        $("#profile-user-department-chef").text(data["user"]["departement"]["chef"]["prenom"] + " " + data["user"]["departement"]["chef"]["nom"]);
                        modals.openSuccessModal(data.message);
                    } else {
                        modals.openErrorModal("Une erreur est survenue lors de la mise à jour du profil : " + data.message);
                    }

                }
            });

        });

        $('#password_form_password_first, #password_form_password_second').on('change', function(e) {
            if ($('#password_form_password_first').val() != $('#password_form_password_second').val()) {
                $('#password_form_password_first')[0].setCustomValidity("Les deux mots de passe doivent correspondre");
            } else {
                $('#password_form_password_first')[0].setCustomValidity("");
            }
        });

        $('#password-update-form').on('submit', function(e) {

            e.preventDefault();

            $.post({
                url: '/api/user/setPassword',
                method: 'POST',
                data: $("#password-update-form").serialize(),
                success: function(data) {

                    if (data.statut == "ok") {
                        modals.openSuccessModal(data.message);
                    } else {
                        modals.openErrorModal("Une erreur est survenue lors du changement du mot de passe : " + data.message.password);
                    }

                }
            });

        });

    });

    $('#user-profile-modal').on('hidden.bs.modal', function (e) {
        $('#password-update-form').off('submit');
        $("#user-update-form").off('submit');
    });


})