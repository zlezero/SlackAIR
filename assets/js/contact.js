import * as modals from './modals';
import 'paginationjs';

/**
 * @author VATHONNE Thomas et CORREA Aminata
 * Ce fichier permet de gérer l'annuaire à savoir l'affichage des contacts, la pagination et l'envoie d'un message en privé
 */

$(function () {

    $('#annuaire-modal').on('show.bs.modal', function (e) {

        $("#listUsers").empty();
        $('#listUsersPagination').empty();
        $('#annuaire-input-group1').val("");

        $.post({
            url: '/api/user/getContacts',
            success: function(data) {

                if (data.statut == "ok") {

                    // Récupération et pagination de la liste des utilisateurs
                    annuairePagination(data.message.users);

                    // Recherche d'un utilisateur par son nom,prenom
                    $('#annuaire-input-group1').on('keyup', function (){
                        let filter = $(this).val().toUpperCase();
                        let name = "";
                        let users = data.message.users;
                        let dataFilter = [];
                        users.forEach((user) => {
                            name = (user.prenom + " "+ user.nom).toUpperCase();
                            if((name.indexOf(filter) > -1)){
                                dataFilter.push(user);
                            }
                        })
                        $("#listUsers").empty();
                        $('#listUsersPagination').empty();
                        annuairePagination(dataFilter);
                    })

                    // Pagination de l'annuaire
                    function annuairePagination(dataToPaginate){
                        $("#listUsersPagination").pagination({
                            dataSource: dataToPaginate,
                            pageSize: 5,
                            className: 'paginationjs-big custom-paginationjs',
                            callback: function(data, pagination){
                                let html = userCardTemplate(data);
                                $('#listUsers').html(html);
                            },
                            beforePageOnClick: function(){
                                $('#send-message-contact').off('click', 'a');
                            },
                            beforePreviousOnClick: function(){
                                $('#send-message-contact').off('click', 'a');
                            },
                            beforeNextOnClick:  function(){
                                $('#send-message-contact').off('click', 'a');
                            },
                            afterIsFirstPage: function(){
                                pageGestion();
                            },
                            afterPreviousOnClick: function(){
                                pageGestion();
                            },
                            afterNextOnClick: function(){
                                pageGestion();
                            },
                            afterPageOnClick: function(){
                                pageGestion();
                            }
                        })
                    }

                    // Affichage de chaque carte utilisateur
                    function userCardTemplate(users){
                        
                        let html = "";
                        users.forEach((user) => {
                            html += '<div class=" card-col col-sm-12"><div class="card shadow user-card w-100"><div class="card-body row"><div class="col-12 col-sm-3 mb-3 mb-sm-0 text-center"><div class="img-content"><img class="thumb-lg rounded-circle bx-s"'
                            + 'src="' + user.photo_de_profile + '" alt="">' 
                            + '<i class="fa fa-circle ' + user.statut.status_color +'"></i></div></div>'
                            + '<div class="col-12 col-sm-9"><div class="send-message-contact p-t-10 btn-group-sm contact-options">'
                            + '<a href="#" class="float-right" data-toggle="tooltip" title="Envoyer un message" data-user-id="' + user.id + '" ><i class="far fa-envelope"></i></a></div>'
                            + '<div class="user-info"><h4>' 
                            + user.prenom + " "+ user.nom
                            +'</h4>' + (user.profession ? '<p class="text-muted">' + user.profession + '</p>' : '') + '</div></div></div></div></div>';
                        });
                        return html;
                    }

                    //Gestion envoi d'un DM
                    function pageGestion(){
                        
                        $('[data-toggle="tooltip"]').tooltip();
                        $('.send-message-contact').on('click', 'a', function(v) {

                            v.preventDefault();
    
                            var contactId = {"userId" : this.getAttribute('data-user-id')};
    
                            if ($('.channel[data-useriddm=' + contactId.userId + ']').length == 0) {
    
                                $.post({
                                    url: '/api/groupe/createDM',
                                    data: contactId,
                                    success: function(data) {
        
                                        if(data.statut == "ok") {
        
                                            $("#collapse-message-private").append('<a href="" data-idchannel="' + data.message.id + '" data-userIdDM='+ data.message.user.id +' data-userstatut="' + data.message.user.statut.status_color + '" class="channel user_channel"><i class="fa fa-circle '+ data.message.user.statut.status_color +'"></i>' + data.message.user.pseudo + '</a>');
                                            
                                            if ($("#collapse-message-private").hasClass("hide")) {
                                                $('.dropdown-btn', $('#collapse-message-private').parent())[0].click();
                                            }
                    
                                            window.subscribeChannel(); //Subscribe à l'event onClick
                                            window.subscribeToChannel(data.message.id); //Subscribe au websocket
    
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
                    }

                } else {
                    openErrorModal("Une erreur est survenue lors du chargement des utilisateurs : " + data.message);
                    $('#annuaire-modal').modal('hide');
                }

            }

        });

    });

    $('#annuaire-modal').on('hidden.bs.modal', function (e) {
        $('#send-message-contact').off('click', 'a');
        $("#listUsersPagination").off('click', 'li a');
    });

});