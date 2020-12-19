/**
 * @author VATHONNE Thomas
 * Cette fonction fait apparaitre le modal de succès
 */
function openSuccessModal(message, onClose) {
    $('#modal_success_content').html(message);
    $('#success_modal').modal('show');
}

/**
 * @author VATHONNE Thomas
 * Cette fonction fait apparaitre le modal d'erreur
 */
function openErrorModal(message, onClose) {
    $('#modal_error_content').html(message);
    $('#error_modal').modal('show');
}

module.exports = {
    openSuccessModal: openSuccessModal,
    openErrorModal: openErrorModal
}