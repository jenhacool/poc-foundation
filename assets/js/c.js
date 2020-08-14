function pocSetCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}

pocUrlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null) {
       return null;
    }
    return decodeURI(results[1]) || 0;
}

//Check if poc exist
if (pocUrlParam('poc')) {
    pocSetCookie('ref_by',pocUrlParam('poc'),360)
    if (pocUrlParam('subid')) {
        pocSetCookie('ref_by_subid',pocUrlParam('subid'),360)
    }
    if (window.history.replaceState) {
        // window.history.replaceState({}, null, pocRemoveParam('poc', window.location.href));
    }
}