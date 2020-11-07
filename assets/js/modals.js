function openSuccessModal(message, onClose) {
    $('#modal_success_content').html(message);
    $('#success_modal').modal('show');
}

function openErrorModal(message, onClose) {
    $('#modal_error_content').html(message);
    $('#error_modal').modal('show');
}

module.exports = {
    openSuccessModal: openSuccessModal,
    openErrorModal: openErrorModal
}