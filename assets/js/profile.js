import * as modals from './modals';
import { openErrorModal } from './modals';

$(function() {

    $('#user-profile-modal').on('show.bs.modal', function (e) {
        
        $("#upload_pdp_pdp").on("change", function(e) {

            e.preventDefault();

            var formData = new FormData();
            formData.append('category', 'general');
            
            var blob = $('#upload_pdp_pdp')[0].files[0];
            formData.append('upload_pdp[pdp]', blob);
            formData.append('upload_pdp[_token]', $('#upload_pdp__token').val());
            
            if ($('#upload_pdp_pdp')[0].files[0].size > 2000000) {
                modals.openErrorModal("Photo non valide ou trop volumineuse (2Mo maximum)");
            } else {

                $.ajax({
                    type:'POST',
                    url: '/api/user/setPdp',
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(data) {
                        
                        if (data.statut == "ok") {
                            $("#pdpUser").attr('src', data.message.photo_de_profile);
                            $("#mainPdp").attr('src', data.message.photo_de_profile);
                        } else {
                            modals.openErrorModal("Une erreur est survenue lors de l'ajout de la photo de profile : " + data.message);
                        }
    
                    }
                });

            }

        });

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
                        if (data.user.departement.chef.prenom) {
                            $("#profile-user-department-chef").text(data["user"]["departement"]["chef"]["prenom"] + " " + data["user"]["departement"]["chef"]["nom"]);
                        }
                        modals.openSuccessModal(data.message);
                    } else {
                        modals.openErrorModal("Une erreur est survenue lors de la mise à jour du profil : " + data.message);
                    }

                }
            });

        });

        $('#api-update-form').on('submit', function(e) {

            e.preventDefault();

            $.post({
                url: '/api/user/generateApiKey',
                data: $("#api-update-form").serialize(),
                success: function(data) {
                    if (data.statut == "ok") {
                        $('#api_form_apikey').val(data.message.user.apiKey);
                    } else {
                        modals.openErrorModal(data.message);
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
        $("#pdp-fileUpload-form").off('submit');
        $("#upload_pdp_pdp").off("change");
        $('#api_form').off('submit');
    });


})