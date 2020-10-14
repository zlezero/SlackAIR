$(function() {

    const socket = WS.connect("ws://localhost:1337");
    var session_glob;
    var current_channel_id = 1;

    socket.on("socket/connect", function (session) {
        console.log("Connexion réussie !");
        session_glob = session;
        // The callback function in "subscribe" is called every time an event is published in that channel.
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
        //addMessage(message.name, message.message);

    });

    $('#changeChannel').on("click", function() {
        if (current_channel_id == 1) {
            current_channel_id = 2;
        } else {
            current_channel_id = 1;
        }
        console.log("New channel id : " + current_channel_id);
    });

    function addMessage(name, message, channel) {
        const messageHTML = "<div class='message'><strong>" + name + ":</strong> " + message + "</div>";
        console.log(channel);
        if (channel == 2) {
            $('#chat2').html($('#chat2').html() + messageHTML);
        } else {
            $('#chat').html($('#chat').html() + messageHTML);
        }
    }

});
