jQuery(document).ready(function(){
    if (!pocGetCookie('_crmuid')) {
        jQuery.get( "/?poc_action=getuid", function( data ) {
            pocSetCookie('_crmuid',data,360)
            setCrmUidToForm(data)
        });
    }else{
        setCrmUidToForm(pocGetCookie('_crmuid'))
    }
});

async function pocSendGet(_url) {
    return await jQuery.ajax({
        url: _url,
    })
    .done(function( data ) {
        return data
    })
}


function pocGetCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return false;
}

function setCrmUidToForm(_crmuid) {
    // jQuery("#"+crmuid_field_name).val(_crmuid)
    //Repeat every 1 second
    setTimeout(function(){
        setCrmUidToForm(_crmuid)
    }, 1000);
}

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

function pocRemoveParam(key, sourceURL) {
    var rtn = sourceURL.split("?")[0],
        param,
        params_arr = [],
        queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
    if (queryString !== "") {
        params_arr = queryString.split("&");
        for (var i = params_arr.length - 1; i >= 0; i -= 1) {
            param = params_arr[i].split("=")[0];
            if (param === key) {
                params_arr.splice(i, 1);
            }
        }
        rtn = rtn + "?" + params_arr.join("&");
    }
    return rtn;
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

//Check if utm_content exist & utm_source doesnot
if (pocUrlParam('utm_content') && (!pocUrlParam('utm_source') || pocUrlParam('utm_source') == "email")) {
    pocSetCookie('_crmuid',pocUrlParam('utm_content'),360)
    if (window.history.replaceState) {
        window.history.replaceState({}, null, pocRemoveParam('utm_content', window.location.href));
    }
}