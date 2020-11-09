import * as modals from './modals';

$(function () {

    $('#annuaire-modal').on('show.bs.modal', function (e) {

        $("#listUsers").empty();

        $.post({
            url: '/api/user/getContacts',
            success: function(data) {

                if (data.statut == "ok") {

                    data.message.users.forEach((user) => {

                        let html = '<div class=" card-col col-sm-12"><div class="card shadow user-card w-100"><div class="card-body row"><div class="col-12 col-sm-3 mb-3 mb-sm-0 text-center"><div class="img-content"><img class="thumb-lg rounded-circle bx-s" src="https://www.bebegavroche.com/media/catalog/product/cache/1/thumbnail/1200x/040ec09b1e35df139433887a97daa66f/S/M/SM327-Masque-en-carton-Jasmine---Disney-Aladdin---27--cm-1.jpg" alt="">' 
                                    + '<i class="fa fa-circle ' + user.statut.status_color +'"></i></div></div>'
                                    + '<div class="col-12 col-sm-9"><div class="send-message-contact p-t-10 btn-group-sm contact-options">'
                                    + '<a href="#" class="float-right" data-toggle="tooltip" title="Envoyer un message" data-user-id="' + user.id + '" ><i class="far fa-envelope"></i></a></div>'
                                    + '<div class="user-info"><h4>' 
                                    + user.prenom + "  "+ user.nom
                                    +'</h4>' + (user.profession ? '<p class="text-muted">' + user.profession + '</p>' : '') + '</div></div></div></div></div>';

                        $("#listUsers").append(html);

                        $('[data-toggle="tooltip"]').tooltip();

                    });

                    $('.send-message-contact').on('click', 'a', function(v) {

                        v.preventDefault();

                        var contactId = {"userId" : this.getAttribute('data-user-id')};

                        if ($('.channel[data-useriddm=' + contactId.userId + ']').length == 0) {

                            $.post({
                                url: '/api/groupe/createDM',
                                data: contactId,
                                success: function(data) {
    
                                    if(data.statut == "ok") {
    
                                        $("#collapse-message-private").append('<a href="" data-idchannel="' + data.message.id + '" data-userIdDM='+ data.message.user.id +' class="channel user_channel"><i class="fa fa-circle '+ data.message.user.statut.status_color +'"></i>' + data.message.user.pseudo + '</a>');
                                        
                                        if ($("#collapse-message-private").hasClass("hide")) {
                                            $('.dropdown-btn', $('#collapse-message-private').parent())[0].click();
                                        }
                
                                        window.subscribeChannel();
                
                                        $(".channel[data-idchannel=" + data.message.id + "]").trigger('click');
                                        $('#annuaire-modal').modal('hide');
                
                                    } else {
                                        modals.openErrorModal("Une erreur est survenue lors de la création du DM : " + data.message);
                                    }
    
                                }
    
                            });

                        } else {

                            $(".channel[data-useriddm=" + contactId.userId + "]").trigger('click');

                            $('#annuaire-modal').modal('hide');

                            if ($("#collapse-message-private").hasClass("hide")) {
                                $('.dropdown-btn', $('#collapse-message-private').parent())[0].click();
                            }

                        }

                    });

                } else {
                    openErrorModal("Une erreur est survenue lors du chargement des utilisateurs : " + data.message);
                    $('#annuaire-modal').modal('hide');
                }

            }

        });

    });

    $('#annuaire-modal').on('hidden.bs.modal', function (e) {
        $('#send-message-contact').off('click', 'a');
    });

});