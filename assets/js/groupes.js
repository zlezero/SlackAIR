//https://benoitgrisot.fr/inserer-un-formulaire-dans-une-modale-avec-symfony-et-materializecss/

import * as modals from './modals';

$(function() {

    $('.searchContact').on('keydown',function(e) {
        var key = e.key || e.keyCode;
        var id=this.id;
        console.log(key);
        if (key === 'Enter' || key === 13) {
            e.preventDefault();
            var vals=$("#invits"+id.slice(-1)+" .create_groupe_invitations").val();
                    $("#invits"+id.slice(-1)+" .create_groupe_invitations>option:contains("+$("#"+this.id).val()+")").each(function(index){
                        var elet=$($("#invits"+id.slice(-1)+" .create_groupe_invitations>option:contains("+$("#"+id).val()+")")[index]);
                        vals.push(elet.val());
                    });
            vals.push($("#invits"+id.slice(-1)+" .create_groupe_invitations>option:contains("+$("#"+id).val()+")").val());
            $("#invits"+id.slice(-1)+" .create_groupe_invitations").val(vals);
            $("#"+id).val("");
        }
    });
    $('.searchContact').on('keyup',function(e) {
        $("#invits"+this.id.slice(-1)+" .create_groupe_invitations>option").css("display", "none");
        $("#invits"+this.id.slice(-1)+" .create_groupe_invitations>option:contains("+$("#"+this.id).val()+")").css("display", "block");
        
    });
    $('.create-grp-modale').on('show.bs.modal', function (e) {
        $(".create_groupe_invitations>option").prop("disabled",true);
        $("#create_groupe_typeGroupeId").val(this.id.slice(-1));
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