$(function() {
    

    //Gestion des emojis

    $("#emojis").disMojiPicker();
    twemoji.parse(document.body);

    $("#emojis").picker(
        emoji => $('#message').val($('#message').val() + emoji)
    );

    $('#emojis').css('display', 'none');

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
            addMessage(payload.pseudo, payload.message, payload.channel);
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

    function addMessage(name, message, channel) {
        const messageHTML = "<div class='col-12'><div class='chat-bubble chat-bubble--left'>" + name + " : " + message + "</div></div>";
        $('#chat-messages').html($('#chat-messages').html() + messageHTML);
    }

    //Autres
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

});
