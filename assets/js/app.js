function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function convertDateToUTC(date) { 
    return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()); 
}

function formatDate(date) {

    let messageDate = new Date(date);

    if (messageDate.getUTCDate() == new Date().getUTCDate()) {
        return "Aujourd'hui à " + messageDate.getHours() + ":" + messageDate.getMinutes();
    } else if (messageDate.getUTCDate() == new Date().getUTCDate() - 1) {
        return "Hier à " + messageDate.getHours() + ":" + messageDate.getMinutes();
    } else {
        return capitalizeFirstLetter(messageDate.toLocaleDateString('fr-FR',  {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'}));
    }
    
}

module.exports = {
    formatDate: formatDate
}