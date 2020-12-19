import * as modals from './modals';
import {formatDate, bytesToSize} from './app';

/**
 * @author CORREA Aminata
 * Ajoute un message au container des messages épinglés en fonction de son type
 */
window.addPinnedMessage = function(name, message, messageTime, id, url_photo_de_profile, media, is_updated) {
    
    let messageHTML = "";
    if(media) {

        if(media.fileLabel == 'Fichier') {

           messageHTML =
           `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 media-message-row" data-id-message='${id}'>
                                    <div class="text-center p-l-5 icon-file">
                                        <i class=" media-icon far fa-file-alt"></i>
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
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" data-pinned-message-id='${id}' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </li>`; 

        } else if(media.fileLabel == 'PDF') {

            messageHTML = 
            `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class='col-sm-12'>
                                <div class='row p-l-5 p-t-10 media-message-row' data-id-message='${id}'>
                                    <div class='text-center p-l-5 icon-file'>
                                        <i class='media-icon far fa-file-pdf'></i>
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
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" data-pinned-message-id='${id}' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </li>`;

        } else if(media.fileLabel == 'Zip') {

            messageHTML =
            `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 media-message-row" data-id-message='${id}'>
                                    <div class="text-center p-l-5 icon-file">
                                        <i class="media-icon far fa-file-archive"></i>
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
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" data-pinned-message-id='${id}' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </li>`;

        } else if (media.fileLabel == 'Image') {

            messageHTML = 
            `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10" data-id-message='${id}'>
                                    <img src="${media.fileName}" style="width: 300px;object-fit: contain;cursor: pointer;" alt=''>   
                                </div>
                            </div>
                            <div id="mediaImageModal" class="modal">
                                <span class="close">&times;</span>
                                <img class="modal-content" id="imagePopUp">
                            </div>
                        </div>
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" data-pinned-message-id='${id}' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </li>`;

        } else if(media.fileLabel == 'Audio') {
            
            messageHTML =
            `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10 media-message-row"  data-id-message='${id}'>
                                    <audio controls>
                                        <source src="${media.fileName}" type="${media.fileMimeType}">
                                        Votre navigateur ne reconnait pas la balise HTML audio.
                                    </audio>
                                </div>
                            </div>
                        </div>
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" aria-hidden="true" data-pinned-message-id='${id}'></i>
                        </div>
                    </div>
                </div>
            </li>`;

        } else {

            messageHTML =
            `<li class="dropdown-item dropdown-pinned-messages">
                <div class="col-12 pl-0 pr-0" style="overflow-x: hidden;">
                    <div class='chat-bubble'>
                        <img class='profile-image' src='${url_photo_de_profile}' alt=''>
                        <div class='media-text'>
                            <h6>${name}
                                <span class='time text-muted small' style="float: right;">
                                    ${formatDate(messageTime)}
                                </span>
                            </h6>
                            <div class="col-sm-12">
                                <div class="row p-l-5 p-t-10 p-b-10"  data-id-message='${id}' style="object-fit: contain;">
                                    <video controls preload="auto">
                                        <source src="${media.fileName}" type="${media.fileMimeType}"></source>
                                        Votre navigateur ne supporte pas la balise HTML video.
                                    </video>
                                </div>
                            </div>
                        </div>
                        <div class="redirect-to-pinned-message">
                            <i class="redirect-message-icon fas fa-chevron-circle-right"></i>
                        </div>
                        <div class="delete-pinned-message">
                            <i class="unpin-message-icon fa fa-times" data-pinned-message-id='${id}' aria-hidden="true"></i>
                        </div>
                    </div>
                </div>
            </li>`;

        }

    } else {
    
        let urlRegex = /https?:\/\/(www\.)?[-a-zA-Z0-9@:%._\+~#=]{1,256}\.[a-zA-Z0-9()]{1,6}\b([-a-zA-Z0-9()@:%_\+.~#?&\/\/=]*)/g;
        message = message.replace(urlRegex, function(url) { return '<a href=' + url + ' target="_blank">' + url + '</a>'});
        
        messageHTML = 
        "<li class='dropdown-item dropdown-pinned-messages'><div class='col-12 pl-0 pr-0' style='overflow-x: hidden;'><div class='chat-bubble'><img class='profile-image' src='" + url_photo_de_profile + "' alt=''><div class='text'><h6>" + name + 
        "<span class='time text-muted small' style='float: right;'>"+ formatDate(messageTime) +"</span></h6><p class='text-muted' data-id-message='" + id + "'>" + message + "</p></div><div class='redirect-to-pinned-message'><i class='redirect-message-icon fas fa-chevron-circle-right'></i></div><div class='delete-pinned-message'><i class='unpin-message-icon fa fa-times'"
        + "data-pinned-message-id='"+id+"' aria-hidden='true'></i></div></div></div></li>";

    }

    $('#pinnedMessagesContainer').append(messageHTML);
    if(is_updated){
        $('[data-id-message="' + id +'"]').html( $('[data-id-message="' + id +'"]').html() + "<small> (modifié)</small>");
    }

}

/**
 * @author CORREA Aminata
 * Gère les options d'un message à savoir la suppression, la modification et l'épinglage
 */
window.gestionOptionsMessage = function(current_message_id, current_user_id, current_channel_id) {

        $("#deleteMessageModal").off('show.bs.modal');
        $("#confirm-delete-message").off('click');
        $("#deleteMessageModal").off('hidden.bs.modal');
        $('#modifyMessageModal').off('show.bs.modal');
        $("#message-update-form").off('submit');
        $("#modifyMessageModal").off('hidden.bs.modal');
        $("#pin-message").off("click");
        
        // Gestion suppression d'un message
        $("#deleteMessageModal").on("show.bs.modal", function(e){
            
            $("#confirm-delete-message").on('click', function(e){
              
                $.post({
                    url: '/api/message/deleteMessage',
                    data: {"message_id": current_message_id, "user_id": current_user_id},
                    success: function(data){
                        if (data.statut == "ok"){
                            $("#deleteMessageModal").modal('hide');
                            modals.openSuccessModal(data.message);
                            $('[data-idmessage="' + current_message_id +'"]').parent().parent().parent().hide();
                        }else{
                            modals.openErrorModal(data.message);
                            $("#deleteMessageModal").modal('hide');
                        }
                    }
                });
            });
            
        });

        $("#deleteMessageModal").on('hidden.bs.modal', function (e) {
            $("#deleteMessageModal").off('show.bs.modal');
            $('#confirm-delete-message').off('click');
        });


        // Gestion modification d'un message
        $('#modifyMessageModal').on('show.bs.modal', function(e) {

            let previousText = $('[data-idmessage="' + current_message_id +'"]').text();
            $("#update_message_message").val(previousText);
            
            $("#message-update-form").on('submit', function(v){
        
                v.preventDefault();
                let newMessage = $("#update_message_message").val();
                $.post({
                    url: '/api/message/setMessage',
                    data: {"message_id": current_message_id, "new_message": newMessage, "user_id": current_user_id},
                    success: function(data){
                        if (data.statut == "ok") {
                            $("#modifyMessageModal").modal('hide');
                            $('[data-idmessage="' + current_message_id +'"]').html(data.message.text + '<small> (modifié)</small>');
                        } else {
                            modals.openErrorModal(data.message);
                            $("#modifyMessageModal").modal('hide');
                        }
                    }
                });

            });
        });

        $("#modifyMessageModal").on('hidden.bs.modal', function (e) {
            $("#modifyMessageModal").off('show.bs.modal');
            $('#message-update-form').off('submit');
        });

        // Gestion épinglage d'un message
        $("#pin-message").on("click", function (e){

            $.post({
                url: '/api/message/pinMessage',
                data: {"message_id": current_message_id, "channel_id": current_channel_id},
                success: function(data){
                    if (data.statut == "ok"){
                        modals.openSuccessModal(data.message);
                    }else{
                        modals.openErrorModal(data.message);
                    }
                }
            }); 

        });

        $("pin-message").off("click");
       
}