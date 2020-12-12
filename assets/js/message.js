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

    //Gestion des utilisateurs en train d'écrire

    var isWriting = [];

    function gestionIsWriting(isWriting) {

        let data = {
            event: {type: isWriting ? 'startWriting' : 'stopWriting'},
            channel: current_channel_id,
        };

        session_glob.publish("channelEvent/" + current_channel_id, {data: JSON.stringify(data)});

    }

    function stopWriting(idUser) {
        clearTimeout(isWriting[idUser].func);
        delete isWriting[idUser];
        updateWritingCSS();
    }

    function startWriting(idUser, pseudo) {
        isWriting[idUser] = {func: setTimeout(stopWriting, 5000, idUser), pseudo: pseudo}
        updateWritingCSS();
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
    var notif_channel_general = false;
    var notif_channel_prive = 0;
    var id_user = $('#id_current_user').data('id-current-user');
    var cache_messages = {};

    socket.on("socket/connect", function (session) {

        console.log("Connexion réussie !");
        session_glob = session;

        $('.channel').each(function (index) {
            if ($(this).data("idchannel") != undefined) {
                subscribeToChannel($(this).data("idchannel"));
            }
        });

        subscribeToUserEvents();

        //Gestion des event sur l'utilisateur
        session.subscribe("privateUserEvent/" + id_user, function (uri, payload) {

            let data = JSON.parse(payload.data);
            console.dir(data);
            if (data.typeEvent == "nouveau_channel") {
                addGroupe(data.data.type_groupe.id, data.data.id, data.data.nom, false, data.data.user.id, data.data.user.statut.status_color, data.data.user.pseudo);
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
    }

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
                $("#collapse-message-private").append('<a href="" data-idchannel="' + idGroupe + '" data-userIdDM='+ idUtilisateur +' class="channel user_channel"><i class="fa fa-circle '+ statusColorUser +'"></i>' + pseudoUser + '</a>');
                if ($("#collapse-message-private").hasClass("hide") && openGroupe) {
                    $('.dropdown-btn', $('#collapse-message-private').parent())[0].click();
                }
            default:
                break;
        }

    }

    window.subscribeToChannel = function subscribeToChannel(idChannel) {

        session_glob.subscribe("message/channel/" + idChannel, function (uri, payload) {
            console.log("Message reçu : ", payload);
            if (payload.message.channel == current_channel_id) {
                addMessage(payload.message.pseudo, payload.message.message, payload.message.messageTime, payload.message.messageId, payload.message.photo_de_profile, payload.message.media);
            }
        });

        session_glob.subscribe("channelEvent/" + idChannel, function(uri, payload) {

            console.log("(Channel event) Message reçu", payload);

            switch (payload.data.event.type) {

                case 'startWriting':
                    
                    if (payload.data.channel == current_channel_id && payload.data.event.valeur != id_user) {
                        startWriting(payload.data.event.valeur, payload.data.event.pseudo);
                    }

                    break;

                case 'stopWriting':

                    if (payload.data.channel == current_channel_id && payload.data.event.valeur != id_user && payload.data.event.valeur in isWriting) {
                        stopWriting(payload.data.event.valeur);
                    }

                    break;

            }

        });

    }

    socket.on("socket/disconnect", function (error) {
        console.log("Déconnecté : " + error.reason + " / Code : " + error.code);
    });

    $("#sendBtn").on("click", function() {
    
        const data = {
            message: $("#message").val(),
            channel: current_channel_id,
            type: "texte"
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
    disableChannelInteraction();

    function disableChannelInteraction() {
        $('#titre_channel').hide();
        $('#description_channel').hide();
        $('#titre_channel_right').hide();
        $('#label_membres_du_groupe').hide();
        $('#message').prop('disabled', true);
        disableEmojis();
    }

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
        
        $.post({
            url: '/api/channel/getMessages',
            data: {"channelId": current_channel_id},
            success: function (result) {
                result.message.messages.forEach((message) => {
                    addMessage(message.pseudo, message.message, message.date.date, message.messageId, message.photo_de_profile, message.media);
                });
                scrollMessageToEnd();
                $("#loading").remove();
                $('#message').prop('disabled', false);
                enableEmojis();
                
                $('#message').off('input');
                $('.message_is_writing_selector').off('keyup', '#message');

                $('#message').on('input', throttle( () => {
                    gestionIsWriting(true);
                }, 5000));

                $('.message_is_writing_selector').on('keyup', '#message', debounce( () => {
                    gestionIsWriting(false);
                }, 1000));

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

    function addMessage(name, message, messageTime, id, url_photo_de_profile, media) {
        
        let scrollAtEnd = isScrollMessageAtEnd();
        let messageHTML = "";

        if(media) {
            
            if(media.fileLabel == 'Fichier') {

               messageHTML =
               `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
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
                                            <p class="text-muted">${media.fileSize} bytes</p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`; 

            } else if(media.fileLabel == 'PDF') {

                messageHTML = 
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
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
                                            <p class="text-muted">${media.fileSize} bytes</p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`;

            } else if(media.fileLabel == 'Zip') {

                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
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
                                            <p class="text-muted">${media.fileSize} bytes</p>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`;

            } else if (media.fileLabel == 'Image') {

                messageHTML = 
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10" data-idMessage='${id}'>
                                    <img src="${media.fileName}" style="width: 300px;object-fit: contain;" alt=''>   
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`;

            } else if(media.fileLabel == 'Audio') {
                
                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10 media-message-row"  data-idMessage='${id}'>
                                    <audio controls>
                                        <source src="${media.fileName}" type="${media.fileMimeType}">
                                        Votre navigateur ne reconnait pas la balise HTML audio.
                                    </audio>
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`;

            } else {

                messageHTML =
                `<div class='col-12'>
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}</h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10"  data-idMessage='${id}'>
                                    <video controls preload="auto" style="height: 300px;">
                                        <source src="${media.fileName}" type="${media.fileMimeType}"></source>
                                        Votre navigateur ne supporte pas la balise HTML video.
                                    </video>
                                </div>
                            </div>
                        </div>
                        <span class='time text-muted small'>
                            ${formatDate(messageTime)}
                        </span>
                    </div>
                </div>`;

            }

        } else {
            
            let urlRegex = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/g;
            message = message.replace(urlRegex, function(url) { return '<a href=' + url + ' target="_blank">' + url + '</a>'});
            
            messageHTML = 
            "<div class='col-12'><div class='chat-bubble'><img class='profile-image' src='" + url_photo_de_profile + "' alt=''><div class='text'><h6>" + name + 
            "</h6><p class='text-muted' data-idMessage='" + id + "'>" + message + "</p></div><span class='time text-muted small'>"
            + formatDate(messageTime) +"</span></div></div>";

        }
        
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

    //Gestion de l'envoi de fichiers
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

});