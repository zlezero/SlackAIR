import {formatDate} from './app';
import {twemoji} from '../plugins/emoji-picker-twemoji/js/twemoji.min.js';
import * as modals from './modals';

$(function() {

    //Gestion des emojis

    $("#emojis").disMojiPicker();
    $('#emojis').css('display', 'none');

    twemoji.parse(document.body);

    $("#emojis").picker(
        emoji => $('#message').val($('#message').val() + emoji)
    );


    $('#btnEmojis').on('click', (e) => {
        $('#emojis').css('display') == "none" ? $('#emojis').css('display', '') : $('#emojis').css('display', 'none');
    });

    $(document).on('mouseup', function(e) {

        var container = $("#emojis");
    
        if(!container.is(e.target) && container.has(e.target).length === 0 && !$('#btnEmojis').is(e.target) && !$('#btnEmojis i').is(e.target)) {
            container.hide();
        }

    });

    //Gestion des messages

    const socket = WS.connect(_WS_URI);
    var session_glob;
    var current_channel_id = 1;
    var notif_channel_general = false;
    var notif_channel_prive = 0;

    var cache_messages = {};

    socket.on("socket/connect", function (session) {

        console.log("Connexion réussie !");
        session_glob = session;

        $('.channel').each(function (index) {
            if ($(this).data("idchannel") != undefined) {
                subscribeToChannel($(this).data("idchannel"));
            }
        });

        $('.channel.user_channel').each(function(index) {
            if ($(this).data("useriddm") != undefined) {
                subscribeToUserEvent($(this).data("useriddm"));
            }
        });

    });

    window.subscribeToChannel = function subscribeToChannel(idChannel) {
        session_glob.subscribe("message/channel/" + idChannel, function (uri, payload) {
            console.log("Message reçu : ", payload);
            if (payload.channel == current_channel_id) {
                addMessage(payload.pseudo, payload.message, payload.messageTime, payload.messageId, payload.photo_de_profile);
            }
        });
    }

    socket.on("socket/disconnect", function (error) {
        console.log("Déconnecté : " + error.reason + " / Code : " + error.code);
    });

    $("#sendBtn").on("click", function() {
    
        const data = {
            message: $("#message").val(),
            channel: current_channel_id
        };

        if ($("#message").val() != "") {
            session_glob.publish("message/channel/" + current_channel_id, {data: JSON.stringify(data)});
            $('#message').val('');
        }

    });

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

    function loadChannel(idChannel, target) {

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
        $.post({
            url: '/api/channel/getMessages',
            data: {"channelId": current_channel_id},
            success: function (result) {
                result.message.messages.forEach((message) => {
                    addMessage(message.pseudo, message.message, message.date.date, message.messageId, message.photo_de_profile);
                });
                scrollMessageToEnd();
                $("#loading").remove();
            }
        });

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

            }
            
        });
        
        $.post({
            url: '/api/channel/getAllUsers',
            data: {"channelId": current_channel_id},
            success: function (result) {
                $('#listeMembres').html("");
                result.message.utilisateurs.forEach((utilisateur) => {
                    subscribeToUserEvent(utilisateur.id);
                    $('#listeMembres').append('<p class="card-text user_channel" data-userid="' + utilisateur.id + '" ><i class="fa fa-circle ' + utilisateur.statut.status_color + ' "></i> ' + utilisateur.pseudo + ' </p>')
                });

            }
        });

    }

    function subscribeToUserEvent(userId) {

        session_glob.subscribe("user/" + userId, function (uri, payload) {
            
            let data = JSON.parse(payload.data);
            console.log("Message reçu (Statut) : ", data);
            
            switch (data.typeEvent) {
                case "statutChange":
                    $('.user_channel[data-userid=' + data.data.user.id + '] i, .user_channel[data-useridDM=' + data.data.user.id + '] i').removeClass().addClass("fa fa-circle " + data.data.statut.status_color);
                    break;
                case "pseudoChange":
                    let statutHTML = $('.user_channel[data-userid=' + data.data.user.id + '] i, .user_channel[data-useridDM=' + data.data.user.id + '] i').prop("outerHTML");
                    $('.user_channel[data-userid=' + data.data.user.id + '], .user_channel[data-useridDM=' + data.data.user.id + ']').html(statutHTML + " " + data.data.user.pseudo);
                    break;
            }

        });

    }

    function unsubscribeToUserEvent(userId) {
        try {
            session_glob.unsubscribe("user/" + userId);
        } catch(error) {}
    }

    function addMessage(name, message, messageTime, id, url_photo_de_profile) {
        
        let scrollAtEnd = isScrollMessageAtEnd();

        const messageHTML = 
        "<div class='col-12'><div class='chat-bubble'><img class='profile-image' src='" + url_photo_de_profile + "' alt=''><div class='text'><h6>" + name + 
        "</h6><p class='text-muted' data-idMessage='" + id + "'>" + message + "</p></div><span class='time text-muted small'>"
        + formatDate(messageTime) +"</span></div></div>";
        
        $('#chat-messages').append(messageHTML);

        //cache_messages[current_channel_id][id] = {"id": id, "pseudo": name, "message": message, "date": formatDate(messageTime)}
        
        if (scrollAtEnd) {
            scrollMessageToEnd();
        }
        
    }

    function scrollMessageToEnd() {
        $('#chat').scrollTop($('#chat').prop("scrollHeight"));
    }

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

    // Gestion du statut
    
    var idleTime = 0;
    var statutId = 1;
    
    var idleInterval = setInterval(timerIncrement, 60000); // 1 minute

    $(this).on('mousemove', function (e) {

        idleTime = 0;

        if(statutId == 5) {
            statutId = 1;
            setStatutAjax(statutId);
        }
        
    });

    $(this).on('keypress', function (e) {

        idleTime = 0;

        if(statutId == 5) {
            statutId = 1;
            setStatutAjax(statutId);
        }

    });

    $("#statusDropright").on('show.bs.dropdown', function() {
        $('#statusDroprightMenu').on("click", function(v) {
            
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

    function timerIncrement() {
        idleTime = idleTime + 1;
        if (idleTime > 14 && statutId == 1) { //15 minutes
            statutId = 5;
            setStatutAjax(statutId);
        }
    }

    function setStatusPrint(name, color){
        $('#user-profile-statut').html('<i class="fa fa-circle ' + color +'"></i><span> ' + name + '</span>');
        $('#user-status').html('<i class="fa fa-circle ' + color +'"></i><span> ' + name + '</span>');
    }

    function setStatutAjax(idStatut) {
        $.post({
            url: '/api/user/setStatut',
            data: {"statutId": idStatut},
            success: function(result){
                setStatusPrint(result.statut.name, result.statut.status_color);
            }
        });
    }

    $(window).on('beforeunload', (e) => {
        e.preventDefault();
        e.returnValue = '';
    })

    /*window.addEventListener('beforeunload', (event) => {
        // Cancel the event as stated by the standard.
        event.preventDefault();
        statutId = 2;
            $.post({
                url: '/api/user/setStatut',
                data: {"statutId": statutId},
                success: function(result){
                    console.log(result["message"]);
                }
            })
        // Chrome requires returnValue to be set.
        event.returnValue = '';
    });*/

    //Gestion du dark theme
    $('#btn-theme, #switch_theme').on('click', (e) => {

        e.preventDefault();
        
        if($('body').hasClass("dark")) {
            $('body, .modal-dialog').removeClass("dark");
        } else {
            $('body, .modal-dialog').addClass("dark");
        }

    });

    //Gestion des discussions favorites
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
                            $("#collapse-favoris").append('<a href="" data-idchannel="' + data.message.id + '" data-userIdDM='+ data.message.user.id +' class="channel user_channel"><i class="fa fa-circle '+ data.message.user.statut.status_color +'"></i>' + data.message.user.pseudo + '</a>');
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

});