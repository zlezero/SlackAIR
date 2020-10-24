import {formatDate} from './app';
import {twemoji} from '../plugins/emoji-picker-twemoji/js/twemoji.min.js';

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

    $(document).on('mouseup', function(e){

        var container = $("#emojis");
    
        if(!container.is(e.target) && container.has(e.target).length === 0){
            container.hide();
        }

    });

    //Gestion des messages

    const socket = WS.connect("ws://localhost:1337");
    var session_glob;
    var current_channel_id = 1;

    socket.on("socket/connect", function (session) {

        console.log("Connexion réussie !");
        session_glob = session;

        session.subscribe("message/channel", function (uri, payload) {
            console.log("Message reçu : ", payload);
            addMessage(payload.pseudo, payload.message, payload.channel, payload.messageTime);
        });

    });

    socket.on("socket/disconnect", function (error) {
        console.log("Déconnecté : " + error.reason + " / Code : " + error.code);
    });

    $("#sendBtn").on("click", function() {
    
        const data = {
            message: $("#message").val(),
            channel: current_channel_id
        };

        session_glob.publish("message/channel", {data: JSON.stringify(data)});
        $('#message').val('');

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

    function addMessage(name, message, channel, messageTime) {
        const messageHTML = 
        "<div class='col-12'><div class='chat-bubble'><img class='profile-image' src='https://i.pinimg.com/originals/62/99/4c/62994ce35676d330091f6039278972f2.png' alt=''><div class='text'><h6>" + name + 
        "</h6><p class='text-muted'>" + message + "</p></div><span class='time text-muted small'>"
        + formatDate(messageTime) +"</span></div></div>";
        $('#chat-messages').html($('#chat-messages').html() + messageHTML);
    }

    var dropdown = document.getElementsByClassName("dropdown-btn");
    var i;
    for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });
    }

    // Gestion du statut
    
    var idleTime = 0;
    var statutId = 1;
    
    var idleInterval = setInterval(timerIncrement, 60000); // 1 minute

    $(this).on('mousemove',function (e) {

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

    $("#statusDropright").on('show.bs.dropdown', function(){
        $('#statusDroprightMenu').on("click", function(v){
            statutId = v.target.attributes["data-id"].nodeValue;
            setStatutAjax(statutId);
        });
    });

    function timerIncrement() {
        console.log(idleTime);
        console.log(statutId);
        idleTime = idleTime + 1;
        if (idleTime > 14 && statutId == 1) { //15 minutes
            statutId = 5;
            setStatutAjax(statutId);
        }
    }

    function setStatusPrint(name, color){
        $('#user-profile-statut').html('<i class="fa fa-circle ' +color+'"></i><span> ' + name + '</span>');
        $('#user-status').html('<i class="fa fa-circle ' +color+'"></i><span> ' + name + '</span>');
    }

    function setStatutAjax(idStatut) {
        $.post({
            url: '/api/user/setStatut',
            data: {"statutId": idStatut},
            success: function(result){
                setStatusPrint(result['statut']['nom'], result['statut']['color'])
            }
        });
    }

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

    
});