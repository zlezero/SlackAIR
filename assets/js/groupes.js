//https://benoitgrisot.fr/inserer-un-formulaire-dans-une-modale-avec-symfony-et-materializecss/

import * as modals from './modals';
import 'paginationjs';

$(function() {

    $('.create-grp-modale').on('show.bs.modal', function (e) {

        $("#create_groupe_typeGroupeId").val($("#create_groupe_typeGroupeId option:first").val());

        $(".groupe-create-form").on('submit', function (f) {

            f.preventDefault();
            
            $.post({
                url: '/api/groupe/createGrp',
                data: $(this).serialize(),
                success: function(data) {

                    if (data.statut == "ok") {

                        switch(data.message.groupe.type) {
                            case 1:
                                $("#collapse-group-public").append('<a href="" data-idchannel="' + data.message.groupe.id + '" class="channel"><i class="fas fa-hashtag"></i>' + data.message.groupe.nom + '</a>');
                                if ($("#collapse-group-public").hasClass("hide")) {
                                    $('.dropdown-btn', $('#collapse-group-public').parent())[0].click();
                                }
                                break;
                            case 2:
                                $("#collapse-group-private").append('<a href="" data-idchannel="' + data.message.groupe.id + '" class="channel"><i class="fas fa-lock"></i>' + data.message.groupe.nom + '</a>');
                                if ($("#collapse-group-private").hasClass("hide")) {
                                    $('.dropdown-btn', $('#collapse-group-private').parent())[0].click();
                                }
                                break;
                            default:
                                break;
                        }

                        $("#create_groupe_nom").val("");
                        $('#create_groupe_description').val("");
                        $("#create_groupe_typeGroupeId").val($("#create_groupe_typeGroupeId option:first").val());
                        $('#create_groupe_invitations').prop('selectedIndex', -1);

                        window.subscribeChannel(); //Subscribe à l'event onClick
                        window.subscribeToChannel(data.message.groupe.id); //Subscribe au websocket

                        $(".channel[data-idchannel=" + data.message.groupe.id + "]").trigger('click');

                        $('.create-grp-modale').modal('hide');

                    } else {
                        modals.openErrorModal("Une erreur est survenue lors de la création du groupe : " + data.message);
                    }
                }
            });

        });

        $('#create_groupe_annuler').on('click', () => {
            $('.create-grp-modale').modal('hide');
        });
        
    });

    $('.create-grp-modale').on('hidden.bs.modal', function (e) {
        $('.groupe-create-form').off('submit');
        $('#create_groupe_annuler').off('click');
    });

});


$(function () {

    $('#create-grp-modale2').on('show.bs.modal', function (e) {

        $("#listGrps").empty();
        $('#listGrpsPagination').empty();
        $('#annuaire-input-group2').val("");

        $.post({
            url: '/api/groupe/getGroupes',
            success: function(data) {

                if (data.statut == "ok") {

                    // Récupération et pagination de la liste des utilisateurs
                    annuairePagination(data.message.groupes);

                    // Recherche d'un utilisateur par son nom,prenom
                    $('#annuaire-input-group2').on('keyup', function (){
                        let filter = $(this).val().toUpperCase();
                        let name = "";
                        let groupes = data.message.groupes;
                        let dataFilter = [];
                        groupes.forEach((groupe) => {
                            name = (groupe.nom).toUpperCase();
                            if((name.indexOf(filter) > -1)){
                                dataFilter.push(groupe);
                            }
                        })
                        $("#listGrps").empty();
                        $('#listGrpsPagination').empty();
                        annuairePagination(dataFilter);
                    })

                    // Pagination de l'annuaire
                    function annuairePagination(dataToPaginate){
                        $("#listGrpsPagination").pagination({
                            dataSource: dataToPaginate,
                            pageSize: 5,
                            className: 'paginationjs-big custom-paginationjs',
                            callback: function(data, pagination){
                                let html = userCardTemplate(data);
                                $('#listGrps').html(html);
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

                    function userCardTemplate(groupes){
                        
                        let html = "";
                        groupes.forEach((groupe) => {
                            html += '<div class=" card-col col-sm-12"><div class="card shadow user-card w-100"><div class="card-body row"><div class="col-12 col-sm-3 mb-3 mb-sm-0 text-center"><div class="img-content"><img class="thumb-lg rounded-circle bx-s"'
                            + '<div class="col-12 col-sm-9"><div class="send-message-grp p-t-10 btn-group-sm contact-options">'
                            + '<a href="#" class="float-right" data-toggle="tooltip" title="Envoyer un message" data-groupe-id="' + groupe.id + '" ><i class="far fa-envelope"></i></a></div>'
                            + '<div class="user-info"><h4>' 
                            + groupe.nom
                            +'</h4>' +  '<p class="text-muted">' + groupe.description + '</p>' + '</div></div></div></div></div>';
                        });
                        return html;
                    }

                    //Gestion envoi d'un DM
                    function pageGestion(){
                        
                        $('[data-toggle="tooltip"]').tooltip();
                        $('.send-message-grp').on('click', 'a', function(v) {

                            v.preventDefault();
    
                            var groupetId = {"groupeId" : this.getAttribute('data-groupe-id')};
    
                            if ($('.channel[data-idChannel=' + groupetId.groupeId + ']').length == 0) {
    
                                $.post({
                                    url: '/api/groupe/createInvit',
                                    data: groupetId,
                                    success: function(data) {
        
                                        if(data.statut == "ok") {
        
                                            $("#collapse-group-public").append('<a href="" data-idchannel="' + data.message.id + '" class="channel"><i class="fas fa-hashtag"></i>' + data.message.nom + '</a>');
                                            
                                            if ($("#collapse-group-public").hasClass("hide")) {
                                                $('.dropdown-btn', $('#collapse-group-public').parent())[0].click();
                                            }
                    
                                            window.subscribeChannel(); //Subscribe à l'event onClick
                                            window.subscribeToChannel(data.message.id); //Subscribe au websocket
    
                                            $(".channel[data-idchannel=" + data.message.id + "]").trigger('click');
                                            $('#create-grp-modale2').modal('hide');
                    
                                        } else {
                                            modals.openErrorModal("Une erreur est survenue lors de l'intégration au groupe : " + data.message);
                                        }
        
                                    }
        
                                });
    
                            } else {
    
                                $(".channel[data-idChannel=" + groupetId.groupeId + "]").trigger('click');
    
                                $('#create-grp-modale2').modal('hide');
    
                                if ($("#collapse-group-public").hasClass("hide")) {
                                    $('.dropdown-btn', $('#collapse-group-public').parent())[0].click();
                                }
    
                            }
    
                        });
                    }

                } else {
                    openErrorModal("Une erreur est survenue lors du chargement des utilisateurs : " + data.message);
                    $('#create-grp-modale2').modal('hide');
                }

            }

        });

    });

    $('#create-grp-modale2').on('hidden.bs.modal', function (e) {
        $('#send-message-grp').off('click', 'a');
        $("#listGrpsPagination").off('click', 'li a');
    });

});