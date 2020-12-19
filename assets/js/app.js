/**
 * @author VATHONNE Thomas
 * Cette fonction met en majuscule la première lettre d'une chaîne de caractère
 */
function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

/**
 * @author VATHONNE Thomas
 * Cette fonction convertit une date sous le format UTC
 */
function convertDateToUTC(date) { 
    return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds()); 
}

/**
 * @author VATHONNE Thomas
 * Cette fonction formate la date d'un message
 */
function formatDate(date) {

    let messageDate = new Date(date);

    if (messageDate.getUTCDate() == new Date().getUTCDate()) {
        return "Aujourd'hui à " + messageDate.getHours() + ":" + (messageDate.getMinutes() < 10 ? '0':'' ) + messageDate.getMinutes();
    } else if (messageDate.getUTCDate() == new Date().getUTCDate() - 1) {
        return "Hier à " + messageDate.getHours() + ":" + (messageDate.getMinutes() < 10 ? '0':'' ) + messageDate.getMinutes();
    } else {
        return capitalizeFirstLetter(messageDate.toLocaleDateString('fr-FR',  {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'}));
    }
    
}

/**
 * @author CORREA Aminata
 * Cette fonction convertit la taille en octets
 */
function bytesToSize(bytes) {
    const sizes = ['Octets', 'KO', 'MO', 'GO', 'TO'];
    if (bytes === 0) return 'n/a';
    const i = parseInt(Math.floor(Math.log(Math.abs(bytes)) / Math.log(1024)), 10);
    if (i === 0) return `${bytes} ${sizes[i]}`;
    return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`;
}

module.exports = {
    formatDate: formatDate,
    bytesToSize: bytesToSize
}