import {formatDate, bytesToSize} from './app';
import {twemoji} from '../plugins/emoji-picker-twemoji/js/twemoji.min.js';
import * as modals from './modals';

$(function() {

    /**
     * @author VATHONNE Thomas
     * Gestion des émojis
     */

    $("#emojis").disMojiPicker();
    $('#emojis').css('display', 'none');

    twemoji.parse(document.body);

    $("#emojis").picker(
        emoji => $('#message').val($('#message').val() + emoji)
    );

    function disableEmojis() {
        $('#btnEmojis').off('click');
    }

    function enableEmojis() {
        $('#btnEmojis').on('click', (e) => {
            $('#emojis').css('display') == "none" ? $('#emojis').css('display', '') : $('#emojis').css('display', 'none');
        });
    }

    $(document).on('mouseup', function(e) {

        var container = $("#emojis");
    
        if(!container.is(e.target) && container.has(e.target).length === 0 && !$('#btnEmojis').is(e.target) && !$('#btnEmojis i').is(e.target)) {
            container.hide();
        }

    });
    $("#btnUpload").on("click",function(){
        $("#upload_file_file").trigger("click");
    })

    /**
     * @author VATHONNE Thomas
     * Gestion des utilisateurs en train d'écrire
     */

    var isWriting = [];

    function gestionIsWriting(isWriting) {

        let data = {
            event: {type: isWriting ? 'startWriting' : 'stopWriting'},
            channel: current_channel_id,
        };

        session_glob.publish("channelEvent/" + current_channel_id, {data: JSON.stringify(data)});

    }

    function stopWriting(idUser) {

        if (isWriting[idUser].func) {
            clearTimeout(isWriting[idUser].func);
        }

        delete isWriting[idUser];

        $('*[data-userid="' + idUser + '"] span').remove();
        $('*[data-userid="' + idUser + '"] i').show();

        updateWritingCSS();

    }

    function startWriting(idUser, pseudo) {

        isWriting[idUser] = {func: setTimeout(stopWriting, 5000, idUser), pseudo: pseudo};

        let statutUser = $('*[data-userid="' + idUser + '"]').data('userstatut');

        $('*[data-userid="' + idUser + '"] i').hide();
        $('*[data-userid="' + idUser + '"] span').remove();

        for (let i = 0; i < 3; i++) {
            $('*[data-userid="' + idUser + '"]').prepend('<span class="dot ' + statutUser + '-background"></span>')
        }

        updateWritingCSS();

    }

    function startWritingDM(idUser) {

        let statutUser = $('*[data-useriddm="' + idUser + '"]').data('userstatut');

        $('*[data-useriddm="' + idUser + '"] i').hide();
        $('*[data-useriddm="' + idUser + '"] span').remove();

        for (let i = 0; i < 3; i++) {
            $('*[data-useriddm="' + idUser + '"]').prepend('<span class="dot ' + statutUser + '-background"></span>')
        }

    }

    function stopWritingDM(idUser) {
        $('*[data-useriddm="' + idUser + '"] span').remove();
        $('*[data-useriddm="' + idUser + '"] i').show();
    }

    function updateWritingCSS() {

        if (Object.keys(isWriting).length == 0) {
            $('.is-typing-wrapper').hide();
        } else if (Object.keys(isWriting).length == 1) {
            $('.is-typing-wrapper').show();
            $('#isTypingText').text(Object.values(isWriting)[0].pseudo + " est en train d'écrire");
        } else if (Object.keys(isWriting).length == 2) {
            $('.is-typing-wrapper').show();
            $('#isTypingText').text(Object.values(isWriting)[0].pseudo + " et " + Object.values(isWriting)[1].pseudo + " sont en train d'écrire");
        } else if (Object.keys(isWriting).length > 2) {
            $('.is-typing-wrapper').show();
            $('#isTypingText').text("Plusieurs personnes sont en train d'écrire");
        }

    }

    //Gestion des messages

    const socket = WS.connect(_WS_URI);
    var session_glob;
    var current_channel_id = -1;
    var id_user = $('#id_current_user').data('id-current-user');
    var channel_souscrit = {};
    var already_subscribe_notif = false;

    socket.on("socket/connect", function (session) {

        console.log("Connexion réussie !");
        session_glob = session;

        $('.channel').each(function (index) {
            if ($(this).data("idchannel") != undefined) {
                subscribeToChannel($(this).data("idchannel"));
            }
        });

        subscribeToUserEvents();

        /**
         * @author VATHONNE Thomas
         * Gestion des events sur l'utilisateur
         */        

        session.subscribe("privateUserEvent/" + id_user, function (uri, payload) {

            let data = JSON.parse(payload.data);
            console.dir(data);
            if (data.typeEvent == "nouveau_channel") {
                addGroupe(data.data.type_groupe.id, data.data.id, data.data.nom, false, data.data.user ? data.data.user.id : null, data.data.user ? data.data.user.statut.status_color : null, data.data.user ? data.data.user.pseudo : null);
                window.subscribeChannel();
                window.subscribeToChannel(data.data.id);
                subscribeToUserEvents();
            }

        });

    });

    function subscribeToUserEvents() {
        $('.channel.user_channel').each(function(index) {
            if ($(this).data("useriddm") != undefined) {
                subscribeToUserEvent($(this).data("useriddm"));
            }
        });
        subscribeToNotif(id_user);
    }

    /**
     * @author VATHONNE Thomas et ZONCHELLO Sébastien
     * Ajout du nouveau groupe au niveau du collapse correspondant
     */ 
    function addGroupe(typeGroupe, idGroupe, nomGroupe, openGroupe, idUtilisateur, statusColorUser, pseudoUser) {
        
        switch(typeGroupe) {
            case 1:
                $("#collapse-group-public").append('<a href="" data-idchannel="' + idGroupe + '" class="channel"><i class="fas fa-hashtag"></i>' + nomGroupe + '</a>');
                if ($("#collapse-group-public").hasClass("hide") && openGroupe) {
                    $('.dropdown-btn', $('#collapse-group-public').parent())[0].click();
                }
                break;
            case 2:
                $("#collapse-group-private").append('<a href="" data-idchannel="' + idGroupe + '" class="channel"><i class="fas fa-lock"></i>' + nomGroupe + '</a>');
                if ($("#collapse-group-private").hasClass("hide") && openGroupe) {
                    $('.dropdown-btn', $('#collapse-group-private').parent())[0].click();
                }
                break;
            case 3:
                $("#collapse-message-private").append('<a href="" data-idchannel="' + idGroupe + '" data-userIdDM='+ idUtilisateur +' data-userstatut="' + statusColorUser + '" class="channel user_channel"><i class="fa fa-circle '+ statusColorUser +'"></i>' + pseudoUser + '</a>');
                if ($("#collapse-message-private").hasClass("hide") && openGroupe) {
                    $('.dropdown-btn', $('#collapse-message-private').parent())[0].click();
                }
            default:
                break;
        }

        subscribeToNotif(id_user);

    }

    /**
     * @author VATHONNE Thomas
     * Gestion des events d'un channel
     */ 
    window.subscribeToChannel = function subscribeToChannel(idChannel) {

        if (!(idChannel in channel_souscrit)) {

            session_glob.subscribe("message/channel/" + idChannel, function (uri, payload) {
    
                console.log("Message reçu : ", payload);
                
                switch (payload.type) {
                    
                    case 'newMessage':

                        if (payload.message.channel == current_channel_id) {
                            addMessage(payload.message.pseudo, payload.message.message, payload.message.messageTime, payload.message.messageId, payload.message.photo_de_profile, payload.message.media, payload.message.is_updated);
                            gestionMessage();
                        } else {
                            addMsgNotification(payload.message.channel, payload.message.pseudo, payload.message.message, payload.message.messageTime, payload.message.photo_de_profile, payload.grpType);
                        }
    
                        break;
    
                    case 'messageSupprime':
                        $('p[data-idmessage=' + payload.message.id + ']').closest('.col-12').remove();
                        break;
    
                    case 'messageModifie':
                        $('p[data-idmessage=' + payload.message.id + ']').text(payload.message.texte).append('<small> (modifié)</small>');
                        break;
    
                    case 'userLeave':
                        
                        $('[data-userid="' + payload.user.id + '"').remove();
                        
                        if (payload.channel.type == 3) {
                            $('[data-useriddm="' + payload.user.id + '"').remove();
                            if (payload.channel.id == current_channel_id) {
                                window.location.reload();
                            }
                        }
    
                        if ($('[data-useriddm="' + payload.user.id + '"').length == 0) {
                            unsubscribeToUserEvent(payload.user.id);
                        }
    
                        break;
    
                }
    
            });
    
            session_glob.subscribe("channelEvent/" + idChannel, function(uri, payload) {
    
                console.log("(Channel event) Message reçu", payload);
    
                switch (payload.data.event.type) {
    
                    case 'startWriting':
                        
                        if (payload.data.channelType == 3 && payload.data.event.valeur != id_user) {
                            startWritingDM(payload.data.event.valeur);
                        }
    
                        if (payload.data.channel == current_channel_id && payload.data.event.valeur != id_user) {
                            startWriting(payload.data.event.valeur, payload.data.event.pseudo);
                        }
    
                        break;
    
                    case 'stopWriting':
    
                        if (payload.data.channelType == 3 && payload.data.event.valeur != id_user) {
                            stopWritingDM(payload.data.event.valeur);
                        }
    
                        if (payload.data.channel == current_channel_id && payload.data.event.valeur != id_user && payload.data.event.valeur in isWriting) {
                            stopWriting(payload.data.event.valeur, payload.data.channelType == 3);
                        }
    
                        break;
    
                    case 'channelTitleUpdate':
                        updateChannelTitle(payload.data.channel, payload.data.event.valeur);
                        break;
    
                    case 'channelDescriptionUpdate':
                        updateChannelDescription(payload.data.channel, payload.data.event.valeur);
                        break;
    
                }
    
            });
    
            channel_souscrit[idChannel] = true;

        }

    }

    socket.on("socket/disconnect", function (error) {
        console.log("Déconnecté : " + error.reason + " / Code : " + error.code);
    });

    /**
     * @author VATHONNE Thomas
     * Modifie le titre d'un channel 
     */ 
    function updateChannelTitle(idChannel, newTitle) {

        if (current_channel_id == idChannel) {
            $('#titre_channel').text(newTitle);
        }

        $('*[data-idchannel="' + idChannel + '"]').contents().last().replaceWith(newTitle);

    }

    /**
     * @author VATHONNE Thomas
     * Modifie la description d'un channel 
     */ 
    function updateChannelDescription(idChannel, newDescription) {
        if (current_channel_id == idChannel) {
            $('#description_channel').text(newDescription);
        }
    }

    /**
     * @author VATHONNE Thomas
     * Diffusion d'un message au moment de l'envoie
     */ 
    $("#sendBtn").on("click", function() {
    
        const data = {
            message: $("#message").val(),
            channel: current_channel_id,
            type: "texte",
            event: {"type": "message"}
        };

        const dataStopWriting = {
            event: {type: 'stopWriting'},
            channel: current_channel_id,
        }
        
        if ($("#message").val() != "" && current_channel_id != -1 && !$('#message').val().includes('<script>')) {
            session_glob.publish("message/channel/" + current_channel_id, {data: JSON.stringify(data)});
            session_glob.publish("channelEvent/" + current_channel_id, {data: JSON.stringify(dataStopWriting)});
            $('#message').val('');
        }

    });

    /**
     * @author VATHONNE Thomas
     * Envoyer d'un message lorsqu'on appuie sur Enter
     */ 
    $('#message').on('keyup', (e) => {

        if (e.defaultPrevented) {
            return;
        }
    
        var key = e.key || e.keyCode;

        if (key === 'Enter' || key === 13) {
            e.preventDefault();
            $('#sendBtn').trigger('click');
        }

    });

    /**
     * @author VATHONNE Thomas
     * Chargement d'un channel
     */ 
    window.subscribeChannel = function subscribeChannel() {

        $(".channel").off("click");

        $(".channel").on("click", (e) => {
        
            e.preventDefault();
    
            let channel = $(e.currentTarget);
    
            if (channel.data('idchannel')) {
                loadChannel(channel.data('idchannel'), channel);
            }
    
        });

    }

    window.subscribeChannel();
    disableChannelInteraction();

    /**
     * @author VATHONNE Thomas
     * Désactive certains fonctionnalités d'un channel
     */ 
    function disableChannelInteraction() {
        $('#titre_channel').hide();
        $('#description_channel').hide();
        $('#titre_channel_right').hide();
        $('#label_membres_du_groupe').hide();
        $('#message').prop('disabled', true);
        $('#upload_file_file').prop('disabled', true);
        $('#pinnedMessagesDropdown').css('pointer-events','none');
        $('#set-favorite').css('pointer-events','none');
        $('#channel-infos-icon').css('pointer-events','none');
        $('#leave-channel-icon').css('pointer-events','none');
        disableEmojis();
    }

    /**
     * @author VATHONNE Thomas et CORREA Aminata
     * Gestion des fonctionnalités lors du chargement de la page
     */ 
    function loadChannel(idChannel, target) {

        disableChannelInteraction();

        $("#chat").append("<img src='https://cdn.dribbble.com/users/415849/screenshots/9782953/crawford_portfolio_loading.gif' id='loading'/>")
        current_channel_id = idChannel;

        $('.channel-selected').removeClass('channel-selected');
        target.addClass("channel-selected");
        $('#chat-messages').html("");

        $('.user_channel').each(function (index) {
            if ($(this).data('userid')) {
                unsubscribeToUserEvent($(this).data('userid'));
            }
        });

        $('[data-toggle="tooltip"]').tooltip();

        /**
         * @author VATHONNE Thomas
         * Récupère les messages du channel et les affiche
         */ 
        $.post({
            url: '/api/channel/getMessages',
            data: {"channelId": current_channel_id},
            success: function (result) {
                result.message.messages.forEach((message) => {
                    addMessage(message.pseudo, message.message, message.date.date, message.messageId, message.photo_de_profile, message.media, message.is_updated);
                });
                scrollMessageToEnd();
                $("#loading").remove();
                $('#message').prop('disabled', false);
                $('#upload_file_file').prop('disabled', false);
                $('#pinnedMessagesDropdown').css('pointer-events','auto');
                $('#set-favorite').css('pointer-events','auto');
                $('#channel-infos-icon').css('pointer-events','auto');
                $('#leave-channel-icon').css('pointer-events','auto');
                enableEmojis();
                
                $('#message').off('input');
                $('.message_is_writing_selector').off('keyup', '#message');

                $('#message').on('input', throttle( () => {
                    gestionIsWriting(true);
                }, 5000));

                $('.message_is_writing_selector').on('keyup', '#message', debounce( () => {
                    gestionIsWriting(false);
                }, 1000));

                popUpMedia();
                gestionMessage();

            }
            
        });

        //Source : https://stackoverflow.com/questions/4220126/run-javascript-function-when-user-finishes-typing-instead-of-on-key-up
        function debounce(callback, wait) {

            let timeout;

            return (...args) => {
                clearTimeout(timeout);
                timeout = setTimeout(function () { callback.apply(this, args) }, wait);
            };

        }

        //Source : https://programmingwithmosh.com/javascript/javascript-throttle-and-debounce-patterns/
        function throttle(callback, interval) {

            let enableCall = true;

            return function(...args) {
                if (!enableCall) return;

                enableCall = false;
                callback.apply(this, args);
                setTimeout(() => enableCall = true, interval);
            }

        }

        /**
         * @author VATHONNE Thomas
         * Récupère les infos du channel
         */ 
        $.post({
            url: '/api/channel/getInfos',
            data: {"channelId": current_channel_id},
            success: function (result) {

                if(result.message.channel.isFavorite) {
                    $("#set-favorite").addClass("saved-channel");
                } else {
                    $("#set-favorite").removeClass("saved-channel");
                }

                if (result.message.channel.type == 3) {
                    $('#titre_channel').text(result.message.channel.user.pseudo);
                    $('#titre_channel_right').text(result.message.channel.user.pseudo);
                    $('#message').attr('placeholder', 'Envoyer un message à ' + result.message.channel.user.pseudo);
                } else {
                    $('#titre_channel').text(result.message.channel.nom);
                    $('#titre_channel_right').text(result.message.channel.nom);
                    $('#message').attr('placeholder', 'Envoyer un message à ' + result.message.channel.nom);
                }
                
                if (result.message.channel.type != 3 && result.message.channel.description != null) {
                    $('#description_channel').show();
                    $('#description_channel').text(result.message.channel.description);
                } else {
                    $('#description_channel').hide();
                    $('#description_channel').text("");
                }

                $('#titre_channel').show();
                $('#titre_channel_right').show();
                $('#label_membres_du_groupe').show();

                /**
                 * @author CORREA Aminata
                 * Gestion des infos du channel à savoir leur affichage et la modification
                 */ 
                $('#channel-infos-icon').off('click');
                $("#contact-infos-modal").off('show.bs.modal');
                $("#contact-infos-modal").off('hidden.bs.modal');
                $("#channel-infos-modal").off('show.bs.modal');
                $("#channel-infos-modal").off('hidden.bs.modal');

                $('#channel-infos-icon').on('click', function(v){
                    
                    if (result.message.channel.type == 3){
                        $("#contact-infos-modal").modal('toggle');
                        $("#contact-infos-loader").show();
                        $("#contact-infos-modal-body").hide();
                    } else{
                        $("#channel-infos-modal").modal('toggle');
                        $("#channel-infos-loader").show();
                        $("#channel-infos-modal-body").hide();
                    }
            
                });

                /**
                 * @author CORREA Aminata
                 * Gestion affichage des infos du contact
                 */ 
                $("#contact-infos-modal").on('show.bs.modal', function(e){
                           
                    $.post({
                        url: '/api/channel/getInfos',
                        data: {"channelId": current_channel_id},
                        success: function (res){
                      
                            $("#contact-pdp-info").attr('src', res.message.channel.other_contact.photo_de_profile);
                            $("#contact-status-info").addClass(res.message.channel.other_contact.statut.status_color);
                            $("#contact-name-info").html(res.message.channel.other_contact.prenom + ' ' + res.message.channel.other_contact.nom);
                            res.message.channel.other_contact.profession ?  $("#contact-profession-info").html(res.message.channel.other_contact.profession) : $("#contact-profession-info").hide();
                            $("#contact-pseudo-info").html('<strong style="color: #000080">Pseudo:</strong> ' + res.message.channel.other_contact.pseudo);
                            $("#contact-mail-info").html('<strong style="color: #000080">Email:</strong> ' + res.message.channel.other_contact.email);
                            res.message.channel.other_contact.age ? $("#contact-age-info").html('<strong style="color: #000080">Age:</strong> ' + res.message.channel.other_contact.age) : $("#contact-age-info").hide();
                            
                            if(res.message.channel.other_contact.departement){
                                $("#contact-departement-name-info").html('<strong style="color: #000080">Département:</strong> ' + res.message.channel.other_contact.departement.nom);
                            }else{
                                $("#contact-departement-name-info").hide();
                            }

                            $("#contact-infos-loader").hide();
                            $("#contact-infos-modal-body").show();
                            
                        }
                    });

                });

                $("#contact-infos-modal").on('hidden.bs.modal', function(e){
 
                   $("#contact-pdp-info").attr('src', '');
                   $("#contact-name-info").html('');
                   $("#contact-profession-info").html('');
                   $("#contact-pseudo-info").html('');
                   $("#contact-mail-info").html('');
                   $("#contact-age-info").html('');
                   $("#contact-departement-name-info").html('');

                });

                /**
                 * @author CORREA Aminata
                 * Gestion affichage des infos du channel
                 */ 
                 $("#channel-infos-modal").on("show.bs.modal", function(e) {

                    $.post({
                        url: '/api/channel/getInfos',
                        data: {"channelId": current_channel_id},
                        success: function (res) {

                            $("#channel-title-info").html(res.message.channel.nom);
                            $("#update_channel_nom").val(res.message.channel.nom);
                            $("#channel-creation-info").html('<i class="far fa-clock pr-1"></i>Créé le ' + formatDate(res.message.channel.date_creation.date));
                            $("#channel-owner-info").html('<strong style="color: #000080">Propriétaire:</strong> ' + res.message.channel.proprietaire.pseudo);
                            
                            if (res.message.channel.type != 3 && res.message.channel.description != null) {
                                $("#channel-description-info").html('<strong style="color: #000080">Description:</strong> ' + res.message.channel.description);
                                $("#update_channel_description").val(res.message.channel.description);
                            } else {
                                $("#channel-description-info").hide();
                            }

                            $("#channel-infos-loader").hide();

                            if(res.message.channel.proprietaire.id != id_user) {
                                $('#edit-infos-tab').parent().hide();
                            } else {
                                $('#edit-infos-tab').parent().show();
                            }

                            $('#channel-infos-tab').tab('show');
                            $("#channel-infos-modal-body").show();

                        }
                        
                    });

                    $("#channel-infos-update-form").on('submit', function(m) {

                        m.preventDefault();
                        let form_data = $("#channel-infos-update-form").serializeArray();
                        form_data.push({name:'channel_id', value: current_channel_id});

                        $.post({
                            url: '/api/channel/setChannelInfos',
                            data: form_data,
                            success: function(data) {
            
                                if (data.statut == "ok") {
                                    $("#channel-title-info").html(data.channel.titre);
                                    $('#titre_channel').text(data.channel.titre);
                                    $('#titre_channel_right').text(data.channel.titre);
                                    $('#message').attr('placeholder', 'Envoyer un message à ' + data.channel.titre);
                                    if(data.channel.description){
                                        $("#channel-description-info").html('<strong style="color: #000080">Description:</strong> ' + data.channel.description);
                                        $('#description_channel').text(data.channel.description);
                                    }
                                    modals.openSuccessModal(data.message);
                                } else {
                                    modals.openErrorModal(data.message);
                                }
            
                            }
                        });

                    });
                    
                });

                $("#channel-infos-modal").on('hidden.bs.modal', function(e) {
                    $("#channel-title-info").html('');
                    $("#update_channel_nom").val('');
                    $("#channel-creation-info").html('');
                    $("#channel-owner-info").html('');
                    $("#channel-description-info").html('');
                    $("#update_channel_description").val('');
                    $('#edit-infos-tab').parent().hide();
                    $("#channel-infos-update-form").off('submit');

                });

            }
            
        });
        
        /**
         * @author VATHONNE Thomas
         * Récupère les membres du channel
         */ 
        $.post({
            url: '/api/channel/getAllUsers',
            data: {"channelId": current_channel_id},
            success: function (result) {
                $('#listeMembres').html("");
                result.message.utilisateurs.forEach((utilisateur) => {
                    subscribeToUserEvent(utilisateur.id);
                    $('#listeMembres').append('<p class="card-text user_channel" data-userid="' + utilisateur.id + '" data-userstatut="' + utilisateur.statut.status_color + '" ><i class="fa fa-circle ' + utilisateur.statut.status_color + ' "></i> ' + utilisateur.pseudo + ' </p>')
                });

            }
        });

    }

    /**
     * @author VATHONNE Thomas
     * Diffuse le statut et le pseudo d'un utilisateur lorsqu'ils sont changés 
     */ 

    function subscribeToUserEvent(userId) {

        session_glob.subscribe("user/" + userId, function (uri, payload) {
        
            let data = JSON.parse(payload.data);
            console.log("Message reçu (Statut) : ", data);
            
            switch (data.typeEvent) {
                case "statutChange":
                    $('.user_channel[data-userid=' + data.data.user.id + '] i, .user_channel[data-useridDM=' + data.data.user.id + '] i').removeClass().addClass("fa fa-circle " + data.data.statut.status_color);
                    $('.user_channel[data-userid=' + data.data.user.id + '], .user_channel[data-useridDM=' + data.data.user.id + ']').attr('data-userstatut', data.data.statut.status_color).data('userstatut', data.data.statut.status_color);
                    break;
                case "pseudoChange":
                    let statutHTML = $('.user_channel[data-userid=' + data.data.user.id + '] i, .user_channel[data-useridDM=' + data.data.user.id + '] i').prop("outerHTML");
                    $('.user_channel[data-userid=' + data.data.user.id + '], .user_channel[data-useridDM=' + data.data.user.id + ']').html(statutHTML + " " + data.data.user.pseudo);
                    break;
            }

        });

    }

     /**
     * @author ZONCHELLO Sébastien et CORREA Aminata
     * Diffuse une notification à l'utilisateur concerné (ajout à un groupe ou nouvel admin d'un groupe)
     */ 
    function subscribeToNotif(userId) {

        if (!already_subscribe_notif) {

            session_glob.subscribe("notif/" + userId, function (uri, payload) {
            
                let data = JSON.parse(payload.data);
                console.log("Notification reçue : ", data);
    
                switch (data.typeEvent) {
                    case "notifGrp":
                        addGrpNotification(data.data.notif.typeGroupeId, data.data.notif.groupeId, data.data.notif.groupe, data.data.notif.dateNotif, data.data.notif.propGrp);
                        break;
                    case "notifAdmin":
                        addAdminNotification(data.data.notif.groupeId, data.data.notif.groupe, data.data.notif.dateNotif);
                        break;
                }
    
            });

            already_subscribe_notif = true;

        }

    }

    function unsubscribeToUserEvent(userId) {
        try {
            session_glob.unsubscribe("user/" + userId);
        } catch(error) {}
    }

    /**
     * @author CORREA Aminata et VATHONNE Thomas
     * Ajoute un message à l'écran en fonction de son type
     */ 
    function addMessage(name, message, messageTime, id, url_photo_de_profile, media, is_updated) {
        
        let scrollAtEnd = isScrollMessageAtEnd();
        let messageHTML = "";
        let messageOptions = 
        `<div class="dropleft dropleft-message" data-message-id="${id}" >
            <a class="text-muted opacity-60 ml-3" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-ellipsis-v"></i></a>
            <div class="messageActionsDropdown dropdown-menu"></div>
        </div>`;

        if (media) {
            
            if(media.fileLabel == 'Fichier') {

               messageHTML =
               `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 media-message-row" data-idMessage='${id}'>
                                    <div class="text-center p-l-5 icon-file">
                                        <i class="far fa-file-alt"></i>
                                    </div>
                                    <div class="col-12 col-sm-9">
                                        <div class="download-button p-t-10 btn-group-sm">
                                            <a class="float-right" href='${media.fileName}' download>
                                                <i class="fas fa-arrow-circle-down"></i>
                                            </a>
                                        </div>
                                        <div>
                                            <a href='${media.fileName}' download>${(media.fileName).split('/')[3]}</a>
                                            <p class="text-muted">${bytesToSize(media.fileSize)} </p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`; 

            } else if(media.fileLabel == 'PDF') {

                messageHTML = 
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class='col-sm-12'>
                                <div class='row p-l-5 p-t-10 media-message-row' data-idMessage='${id}'>
                                    <div class='text-center p-l-5 icon-file'>
                                        <i class='far fa-file-pdf'></i>
                                    </div>
                                    <div class='col-12 col-sm-9'>
                                        <div class='download-button p-t-10 btn-group-sm'>
                                            <a class='float-right' href='${media.fileName}' download>
                                                <i class="fas fa-arrow-circle-down"></i>
                                            </a>
                                        </div>
                                        <div>
                                            <a href='${media.fileName}' download>${(media.fileName).split('/')[3]}</a>
                                            <p class="text-muted">${bytesToSize(media.fileSize)}</p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`;

            } else if(media.fileLabel == 'Zip') {

                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 media-message-row" data-idMessage='${id}'>
                                    <div class="text-center p-l-5 icon-file">
                                        <i class="far fa-file-archive"></i>
                                    </div>
                                    <div class="col-12 col-sm-9">
                                        <div class="download-button p-t-10 btn-group-sm">
                                            <a class="float-right" href='${media.fileName}' download>
                                                <i class="fas fa-arrow-circle-down"></i>
                                            </a>
                                        </div>
                                        <div>
                                            <a href='${media.fileName}' download>${(media.fileName).split('/')[3]}</a>
                                            <p class="text-muted">${bytesToSize(media.fileSize)}</p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`;

            } else if (media.fileLabel == 'Image') {

                messageHTML = 
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10" data-idMessage='${id}'>
                                    <img src="${media.fileName}" style="width: 300px;object-fit: contain;cursor: pointer;" alt=''>   
                                </div>
                            </div>
                            <div id="mediaImageModal" class="modal">
                                <span class="close">&times;</span>
                                <img class="modal-content" id="imagePopUp">
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`;

            } else if(media.fileLabel == 'Audio') {
                
                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10 media-message-row"  data-idMessage='${id}'>
                                    <audio controls>
                                        <source src="${media.fileName}" type="${media.fileMimeType}">
                                        Votre navigateur ne reconnait pas la balise HTML audio.
                                    </audio>
                                </div>
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`;

            } else {

                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style='float: right;'>
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10"  data-idMessage='${id}' style="object-fit: contain;">
                                    <video controls preload="auto">
                                        <source src="${media.fileName}" type="${media.fileMimeType}"></source>
                                        Votre navigateur ne supporte pas la balise HTML video.
                                    </video>
                                </div>
                            </div>
                        </div>
                        ${messageOptions}
                    </div>
                </div>`;

            }

        } else {
            
            let urlRegex = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/g;
            message = message.replace(urlRegex, function(url) { return '<a href=' + url + ' target="_blank">' + url + '</a>'});
            
            messageHTML = 
            "<div class='col-12'><div class='chat-bubble'><img class='profile-image' src='" + url_photo_de_profile + "' alt=''><div class='text'><h6> " + name + 
            "<span class='time text-muted small' style='float: right;'>"+ formatDate(messageTime) +"</span></h6><p class='text-muted' data-idMessage='" + id + "'>" + message + "</p></div>"
            + messageOptions + "</div></div>";

        }
        
        $('#chat-messages').append(messageHTML);
        if(is_updated){
            $('[data-idmessage="' + id +'"]').html( $('[data-idmessage="' + id +'"]').html() + "<small> (modifié)</small>");
        }
        popUpMedia();

        //cache_messages[current_channel_id][id] = {"id": id, "pseudo": name, "message": message, "date": formatDate(messageTime)}
        if (scrollAtEnd) {
            scrollMessageToEnd();
        }
        
    }

     /**
     * @author CORREA Aminata
     * Affiche une image en modal lorsqu'on clique dessus
     */ 
    function popUpMedia() {

        let modal = $('#mediaImageModal');
        let img = $('.media-text img');
        let modalImg = $('#imagePopUp');

        img.off('click');
        $('#mediaImageModal .close, #mediaImageModal').off('click');

        img.on('click', (e) => {
            modal.show();
            modalImg.attr('src', $(e.currentTarget).attr('src'));
        });

        $('#mediaImageModal .close, #mediaImageModal').on('click', () => {
            modal.hide();
        });
    }

     /**
     * @author CORREA Aminata
     * Ajoute à un message ses différentes options en fonction de son auteur et de son type
     */ 
    function gestionMessage() {

        $('.dropleft-message').off('show.bs.dropdown');
        
        $('.dropleft-message').on('show.bs.dropdown', function(e){

            let current_message_id = $(this).data('message-id');
            var menu = "";
            let messageDropdown = $(this);

            $.post({
                url: '/api/message/checkMessageOptions',
                data: {"message_id": current_message_id, "channel_id": current_channel_id},
                success: function(data){

                    if (data.statut == "ok") {

                        if(data.message.estMedia == true) {

                            if(data.message.userId != id_user) {
                                menu = `<a id="pin-message" class="dropdown-item d-flex align-items-center" href="#">Epingler le message <i class="fas fa-thumbtack"></i></a>`;
                            } else {
                                menu = `<a id="pin-message" class="dropdown-item d-flex align-items-center" href="#">
                                            Epingler le message <i class="fas fa-thumbtack"></i>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a id="delete-message" class="dropdown-item d-flex align-items-center" data-toggle="modal" href="#deleteMessageModal">
                                            Supprimer le message <i class="far fa-trash-alt"></i>
                                        </a>`;
                            }
                        } else{

                            if(data.message.userId != id_user) {
                                menu = `<a id="pin-message" class="dropdown-item d-flex align-items-center" href="#">Epingler le message <i class="fas fa-thumbtack"></i></a>`;
                            } else {
                                menu = `<a id="modify-message" class="dropdown-item d-flex align-items-center" data-toggle="modal" href="#modifyMessageModal">
                                            Modifier le message <i class="fas fa-pencil-alt"></i>
                                        </a>
                                        <a id="pin-message" class="dropdown-item d-flex align-items-center" href="#">
                                            Epingler le message <i class="fas fa-thumbtack"></i>
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <a id="delete-message" class="dropdown-item d-flex align-items-center" data-toggle="modal" href="#deleteMessageModal">
                                            Supprimer le message <i class="far fa-trash-alt"></i>
                                        </a>`;
                            }

                        }

                        if(messageDropdown.children('.messageActionsDropdown').children().length == 0)
                            messageDropdown.children('.messageActionsDropdown').append(menu);

                        window.gestionOptionsMessage(current_message_id, id_user, current_channel_id);

                    } else {
                        modals.openErrorModal(data.message);
                    }

                }
            }); 
            
        });

        $('.dropleft-message').on('hide.bs.dropdown', function(e) {
            $(this).children('.messageActionsDropdown').empty();
        });

    }

    /**
     * @author ZONCHELLO Sébastien
     * Gestion des notifications
     */ 
    $(".notification").off('click');
    $(".notification").on("click", function() {

        let id = this.id;

        let channid = $("#" + id).data("idchannel");
        let chann = $(".channel[data-idchannel='" + channid + "']");
        let select = chann.parent();

        if (select.hasClass("hide")) {
            $('.dropdown-btn', select.parent())[0].click();
        }

        $(".channel[data-idchannel='" + channid + "']").trigger("click");

        if($("#" + id).parent().hasClass("container-messages")) {
            
            $.post({
                url: '/api/notification/readNotifMsg',
                data: {"groupeId": channid},
                success: function(result){
                    changeNotifNb("notif-msg-count",result.nbNotifs);
                }
            });

        } else {

            $.post({
                url: '/api/notification/readNotifGrp',
                data: {"groupeId": channid},
                success: function(result){
                    changeNotifNb("notif-count",result.nbNotifs);
                }
            });

        }

        $("#" + id).remove();

    });

    /**
     * @author ZONCHELLO Sébastien
     * Ajoute une notification d'un nouveau message reçu
     */ 
    function addMsgNotification(channel, pseudo, message, messageTime, pdp, grpTypeId) {

        let iconHTML = '';
        let titreChannel = '';

        if(grpTypeId == 3) {
            titreChannel = pseudo;
            iconHTML = `<div class="notifications__item__avatar"><img src="${pdp}" /></div>`;
        } else {
            titreChannel = $(".channel[data-idchannel='" + channel + "']")[0].innerText;
            iconHTML = '<div><span class="fa-stack fa-2x"><i class="fa fa-circle fa-stack-2x icon-background"></i><i class="fas fa-comments fa-stack-1x"></i></span></div>';
        }

        let messageHTML = 
        '<div class="dropdown-item btn notification" data-idchannel="' + channel + '" id="notif' + channel + '"><div class="d-flex">'
        + iconHTML + '<div class="message-content"><div class="message-title">'
        +'<strong>'+ titreChannel + '</strong>'
        +'</div><div class="message-detail">' + ( $("#notif" + channel).length < 1 ? 'Nouveau message' : 'Nouveaux messages') + '</div>'
        +'<a data-idchannel="' + channel + '">Consulter la discussion</a>'
        +'</div><span class="text-muted small">' + formatDate(messageTime) + '</span></div></div>';

        if($("#notif" + channel).length < 1) {
            increaseNotifNb("notif-msg-count");
        } else {
            $("#notif"+ channel).remove();
        }

        $(".container-messages").after(messageHTML);

        $("#notif" + channel).on("click", (e) => {

            let channid = $("#notif" + channel).data("idchannel");

            let chann = $(".channel[data-idchannel='" + channid + "']");
            let select = chann.parent();
            

            $.post({
                url: '/api/notification/readNotifMsg',
                data: {"groupeId": channid},
                success: function(result){
                    changeNotifNb("notif-msg-count", result.nbNotifs);
                }
            });

            if (select.hasClass("hide")) {
                $('.dropdown-btn', select.parent())[0].click();
            }

            $(".channel[data-idchannel='" + channid + "']").trigger("click");
            $("#notif" + channel).off('click');
            $("#notif" + channel).remove();

        });

        playsound();

    }

    /**
     * @author ZONCHELLO Sébastien
     * Ajoute une notification d'une invitation à un groupe
     */ 
    function addGrpNotification(typeChannelId, channelId, channel, messageTime, propGrp) {

        let iconHTML = '';

        if(typeChannelId == 3){
            iconHTML = `<div class="notifications__item__avatar"><img src="${propGrp.photo_de_profile}" /></div>`;
        } else{
            iconHTML = '<div><span class="fa-stack fa-2x"><i class="fa fa-circle fa-stack-2x cheers-icon-background"></i><i class="fas fa-glass-cheers fa-stack-1x"></i></span></div>';
        }

        let notifHTML = 
        '<div class="dropdown-item btn notification" data-idchannel="' + channelId + '" id="notif' + channelId + '"><div class="d-flex">'
        + iconHTML +'<div class="message-content"><div class="message-title"><strong>'+ channel +'</strong>'
        +'</div><div class="message-detail">'
        +'Vous avez été invité à '+ ( typeChannelId == 3 ? 'discuter avec ' : 'rejoindre le groupe ' ) + channel
        +'</div><a data-idchannel="' + channelId + '">Consulter le channel</a>'
        +'</div><span class="text-muted small">'
        + formatDate(messageTime)
        +'</span></div></div>';

        $(".container-groupes").after(notifHTML);

        increaseNotifNb("notif-count");

        $("#notif" + channelId).on("click", (e) => {

            let channid = $("#notif"+channelId).data("idchannel");
            let chann = $(".channel[data-idchannel='"+channid+"']");
            let select = chann.parent();

            if (select.hasClass("hide")){
                $('.dropdown-btn', select.parent())[0].click();
            }

            $(".channel[data-idchannel='"+channid+"']").trigger("click");

            $("#notif" + channelId).remove();
            
            $.post({
                url: '/api/notification/readNotifGrp',
                data: {"groupeId": channid},
                success: function(result){
                    changeNotifNb("notif-count", result.nbNotifs);
                }
            });

        });

        playsound();
    
    }

    /**
     * @author CORREA Aminata
     * Ajoute une notification d'un groupe dont on est le nouvel administrateur
     */ 
    function addAdminNotification(channelId, channelName, messageTime) {

        let notifHTML = 
        '<div class="dropdown-item btn notification" data-idchannel="' + channelId + '" id="notif' + channelId + '"><div class="d-flex">'
        +'<div><span class="fa-stack fa-2x"><i class="fa fa-circle fa-stack-2x admin-icon-background"></i><i class="fas fa-user-shield fa-stack-1x"></i></span></div>'
        +'<div class="message-content"><div class="message-title"><strong>'+channelName+'</strong>'
        +'</div><div class="message-detail">'
        +"Vous êtes désormais l'administrateur du channel " + channelName
        +'</div><a data-idchannel="' + channelId + '">Consulter la discussion</a>'
        +'</div><span class="text-muted small">'
        + formatDate(messageTime)
        +'</span></div></div>';

        $(".container-groupes").after(notifHTML);

        increaseNotifNb("notif-count");

        $("#notif" + channelId).on("click", (e) => {

            let channid = $("#notif"+channelId).data("idchannel");
            let chann = $(".channel[data-idchannel='"+channid+"']");
            let select = chann.parent();

            if (select.hasClass("hide")){
                $('.dropdown-btn', select.parent())[0].click();
            }

            $(".channel[data-idchannel='"+channid+"']").trigger("click");

            $("#notif" + channelId).remove();
            
            $.post({
                url: '/api/notification/readNotifGrp',
                data: {"groupeId": channid},
                success: function(result){
                    changeNotifNb("notif-count", result.nbNotifs);
                }
            });

        });

        playsound();

    }

    /**
     * @author ZONCHELLO Sébastien
     * Incrémente le nombre de notifications à l'écran
     */ 
    function increaseNotifNb(typeNotif) {
    
        let notifnb = $("#" + typeNotif).text();
    
        if(!notifnb.includes("+")){
            changeNotifNb(typeNotif, parseInt(notifnb) + 1);
        }
    
    }

    /**
     * @author ZONCHELLO Sébastien
     * Change le nombre de notifications à l'écran
     */ 
    function changeNotifNb(typeNotif, nbnotif){

        if(parseInt(nbnotif) <= 9){
            $("#" + typeNotif).text((nbnotif).toString());
        }
        else {
            $("#" + typeNotif).text("9+");
        }

    }

    /**
     * @author VATHONNE Thomas
     * Scroll vers le bas du cadre des messages
     */ 
    function scrollMessageToEnd() {
        $('#chat').scrollTop($('#chat').prop("scrollHeight"));
    }

    /**
     * @author VATHONNE Thomas
     * Vérifie si la barre de scroll est bien en bas
     */ 
    function isScrollMessageAtEnd() {
        return $('#chat').scrollTop() + $('#chat').innerHeight() >= $('#chat')[0].scrollHeight;
    }

    $('.dropdown-btn').on('click', (e) => {

        let dropdownContent = $(e.currentTarget).next();
        let icon = $('.icon-caret', e.currentTarget);

        if (dropdownContent.hasClass("hide")) {
            dropdownContent.removeClass("hide");
            dropdownContent.addClass("reveal");
            icon.removeClass("fa-caret-down");
            icon.addClass("fa-caret-up");
        } else {
            icon.removeClass("fa-caret-up");
            icon.addClass("fa-caret-down");
            dropdownContent.removeClass("reveal");
            dropdownContent.addClass("hide");
        }

    });

    /**
     * @author CORREA Aminata
     * Gestion des messages épinglés
     */ 
    $('#pinnedMessagesDropdown').on('show.bs.dropdown', function(e) {

        $("#pinnedMessagesLoader").show();
        $("#noPinnedMessages").hide();
        $('#pinnedMessagesContainer').empty();

        /**
         * @author CORREA Aminata
         * Récupère les messages épinglés d'un channel
         */
        $.post({
            url: '/api/channel/getPinnedMessages',
            data: {"channelId": current_channel_id},
            success: function (result) {

                if(result.message.messages.length > 0) {

                    $("#pinnedMessagesLoader").hide();
                    result.message.messages.forEach((message) => {
                        window.addPinnedMessage(message.pseudo, message.message, message.date.date, message.messageId, message.photo_de_profile, message.media, message.is_updated);
                    });

                } else {
                    $("#pinnedMessagesLoader").hide();
                    $("#noPinnedMessages").show();
                }

                /**
                 * @author CORREA Aminata
                 * Gestion détachement d'un message
                 */ 
                $('.unpin-message-icon').on('click', function(e) {
                    
                    e.preventDefault();
                    e.stopPropagation();

                    $.post({
                        url: '/api/message/unpinMessage',
                        data: {"message_id": $(this).data('pinned-message-id'), "channel_id": current_channel_id},
                        success: function(data){
                            if (data.statut == "ok") {
                                $('#pinnedMessagesDropdown').trigger('show.bs.dropdown');
                            } else {
                                modals.openErrorModal(data.message);
                            }
                        }
                    }); 
                });

                /**
                 * @author CORREA Aminata
                 * Gestion redirection vers un message
                 */
                $('.redirect-message-icon').on('click', function(e) {
                    
                    e.preventDefault();
                    let pinnedMessageId = $(this).parent().siblings('.delete-pinned-message').children('.unpin-message-icon').data('pinned-message-id');
                    
                    $('#chat').animate({
                        scrollTop: $('[data-idMessage="' + pinnedMessageId +'"]').closest('.chat-bubble').offset().top - $('#chat').offset().top + $('#chat').scrollTop()
                    },500, function(){
                        $('[data-idMessage="' + pinnedMessageId +'"]').closest('.chat-bubble').addClass('scroll-message-animation');
                        setTimeout(() => {$('[data-idMessage="' + pinnedMessageId +'"]').closest('.chat-bubble').removeClass('scroll-message-animation');},4000);
                    });
            
                });
            }
        });

    });

    $('#pinnedMessagesDropdown').on('hide.bs.dropdown', function(e){
        $('.unpin-message-icon').off('click');
        $('.redirect-message-icon').off('click');
    });

    /**
     * @author CORREA Aminata
     * Gestion de la sortie d'un channel
     */
    $('#leaveChannelModal').off('show.bs.modal');
    $("#confirm-leave-channel").off('click');
    $('#leaveChannelModal').off('hidden.bs.modal');

    $('#leaveChannelModal').on('show.bs.modal', function(e){
        
        $("#confirm-leave-channel").on('click', function(v){
            
            $.post({
                url: '/api/channel/leaveChannel',
                data: {"channel_id": current_channel_id},
                success: function(data){
                    if (data.statut == "ok"){
                        $("#leaveChannelModal").modal('hide');
                        location.reload();
                    }else{
                        modals.openErrorModal(data.message);
                        $("#leaveChannelModal").modal('hide');
                    }
                }
            });

        });
    });

    $("#leaveChannelModal").on('hidden.bs.modal', function (e) {
        $("#leaveChannelModal").off('show.bs.modal');
        $('#confirm-leave-channel').off('click');
    });
    

    /**
     * @author VATHONNE Thomas et CORREA Aminata
     * Gestion du statut
     */
    var idleTime = 0;
    var statutId = 1;
    
    var idleInterval = setInterval(timerIncrement, 60000); // 1 minute

    /**
     * @author CORREA Aminata
     * Met le statut de l'utilisateur "En ligne" lorsque ce dernier était "Absent" et qu'il bouge la souris
     */
    $(this).on('mousemove', function (e) {

        idleTime = 0;

        if(statutId == 5) {
            statutId = 1;
            setStatutAjax(statutId);
        }
        
    });

    /**
     * @author CORREA Aminata
     * Met le statut de l'utilisateur en "En ligne" lorsque ce dernier était "Absent" et qu'il appuie sur une touche du clavier
     */
    $(this).on('keypress', function (e) {

        idleTime = 0;

        if(statutId == 5) {
            statutId = 1;
            setStatutAjax(statutId);
        }

    });

    /**
     * @author CORREA Aminata
     * Gère le changement de statut d'un utilisateur
     */
    $("#statusDropright").on('show.bs.dropdown', function() {

        $('#statusDroprightMenu :not(.dropdown-header)').on("click", function(v) {
            
            statutId = $(v.target).data("id");
            
            if (statutId == undefined) {
                statutId = $(v.target.parentNode).data('id');
            }
            
            setStatutAjax(statutId);
        
        });

    });

    $("#statusDropright").on('hide.bs.dropdown', function() {
        $('#statusDroprightMenu').off('click');
    });

    /**
     * @author CORREA Aminata
     * Met le statut de l'utilisateur en "Absent" au bout de 15 minutes d'inactivité
     */
    function timerIncrement() {
        idleTime = idleTime + 1;
        if (idleTime > 14 && statutId == 1) { //15 minutes
            statutId = 5;
            setStatutAjax(statutId);
        }
    }

    /**
     * @author CORREA Aminata
     * Affiche le statut de l'utilisateur
     */
    function setStatusPrint(name, color){
        $('#user-profile-statut').html('<i class="fa fa-circle ' + color +'"></i><span> ' + name + '</span>');
        $('#user-status').html('<i class="fa fa-circle ' + color +'"></i><span> ' + name + '</span>');
    }

    /**
     * @author CORREA Aminata
     * Modifie le statut de l'utilisateur et le met à jour à l'écran
     */
    function setStatutAjax(idStatut) {
        $.post({
            url: '/api/user/setStatut',
            data: {"statutId": idStatut},
            success: function(result){
                setStatusPrint(result.statut.name, result.statut.status_color);
            }
        });
    }

    /**
     * @author VATHONNE Thomas
     * Met le statut de l'utilisateur en "Hors ligne" lorsqu'il se déconnecte
     */
    $(window).on('beforeunload', (e) => {
        e.preventDefault();
        e.returnValue = '';
    })

    $(window).on('beforeunload', (event) => {

        event.preventDefault();

        $.post({
            url: '/api/user/setStatut',
            data: {"statutId": 2},
            success: function(result) {
                statutId = 2;
            }
        });

    });

    /**
     * @author ZONCHELLO Sébastien
     * Gestion du dark theme
     */
    $('#btn-theme, #switch_theme').on('click', (e) => {

        e.preventDefault();
        
        if($('body').hasClass("dark")) {
            $('body, .modal-dialog').removeClass("dark");
        } else {
            $('body, .modal-dialog').addClass("dark");
        }

    });

    /**
     * @author CORREA Aminata
     * Gestion des discussions favorites à savoir l'ajout d'un channel aux favoris et la suppression d'un channel des favoris
     */
    $("#set-favorite").on('click', (e) => {

        if($("#set-favorite").hasClass("saved-channel")) {

            $.post({
                url: '/api/user/removeInvitationShortcut',
                data: {'currentChannelId': current_channel_id},
                success: function(data) {

                    if(data.statut == "ok") {

                        if(data.message.groupe) {
                            var findElement = ".channel[data-idchannel=" + data.message.groupe.id + "]";
                            $("#collapse-favoris").find(findElement).remove();
                        } else {
                            var findElement = ".channel[data-idchannel=" + data.message.id + "]";
                            $("#collapse-favoris").find(findElement).remove();
                        }

                        if ($("#collapse-favoris").hasClass("hide")) {
                            $('.dropdown-btn', $('#collapse-favoris').parent())[0].click();
                        }

                        $("#set-favorite").removeClass("saved-channel");
                        window.subscribeChannel();

                    } else {
                        modals.openErrorModal("Une erreur est survenue lors de la suppression d'un channel favori : " + data.message);
                    }
                }
            });

        } else {

            $.post({
                url: '/api/user/setInvitationShortcut',
                data: {'currentChannelId': current_channel_id},
                success: function(data) {

                    if(data.statut == "ok") {

                        if(data.message.groupe) {
    
                            switch(data.message.groupe.type) {
                                case 1:
                                    $("#collapse-favoris").append('<a href="" data-idchannel="' + data.message.groupe.id + '" class="channel"><i class="fas fa-hashtag"></i>' + data.message.groupe.nom + '</a>');
                                    break;
                                case 2:
                                    $("#collapse-favoris").append('<a href="" data-idchannel="' + data.message.groupe.id + '" class="channel"><i class="fas fa-lock"></i>' + data.message.groupe.nom + '</a>');
                                    break;
                                default:
                                    break;
                            }
    
                        } else {
                            $("#collapse-favoris").append('<a href="" data-idchannel="' + data.message.id + '" data-userIdDM='+ data.message.user.id +' data-userstatut="' + data.message.user.statut.status_color + '" class="channel user_channel"><i class="fa fa-circle '+ data.message.user.statut.status_color +'"></i>' + data.message.user.pseudo + '</a>');
                        }
    
                        if ($("#collapse-favoris").hasClass("hide")) {
                            $('.dropdown-btn', $('#collapse-favoris').parent())[0].click();
                        }
    
                        $("#set-favorite").addClass("saved-channel");
                        window.subscribeChannel();
    
                    } else {
                        modals.openErrorModal("Une erreur est survenue lors de l'ajout aux favoris : " + data.message);
                    }
                }
            });

        }
        
    });

    /**
     * @author CORREA Aminata et VATHONNE Thomas
     * Gestion de l'envoi d'un média
     */
    $("#upload_file_file").on("change", function(e) {

        e.preventDefault();

        var formData = new FormData();
        formData.append('category', 'general');
        
        var blob = $('#upload_file_file')[0].files[0];
        formData.append('upload_file[file]', blob);
        formData.append('groupe_id', current_channel_id);
        formData.append('upload_file[_token]', $('#upload_file__token').val());

        if ($('#upload_file_file')[0].files[0].size > 8000000) {
            modals.openErrorModal("Fichier non valide ou trop volumineux (8Mo maximum)");
        } else {

            $.post({
                url: '/api/message/sendMediaMessage',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data){
    
                    if (data.statut != "ok") {
                        modals.openErrorModal("Une erreur est survenue lors de l'ajout du fichier : " + data.message);
                    }
    
                }
            });

        }

    });

    /**
     * @author ZONCHELLO Sébastien
     * Joue le son d'une notification
     */
    function playsound() {
        if(statutId != 4) {
            var audio = $("#notif-sound")[0];
            audio.play();
        }
    }

});