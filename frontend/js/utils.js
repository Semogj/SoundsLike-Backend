


/**
  * Returns true if the parameter is defined and not null.
  */
function isset(variable){
    var type = jQuery.type(variable);
    return type != "undefined" && type != "null"; 
}
/**
  * Returns the first parameter if it is defined and not null, otherwise returns the second.
  * For most situations, var x = x || default will work, but if you are expecting x = 0, this is a stronger solution.
  */
function issetDefault(variable, defaultVal){
    return isset(variable) ? variable : defaultVal;
}
            
            
function getProperty(variable, property, defaultVal){
    var vtype = jQuery.type(variable);
    if( jQuery.type(property) != "string" && vtype == "array" || vtype == "object")
        return property in variable ? variable[property] : typeof defaultVal != "undefined" ? defaultVal : null;
    return typeof defaultVal != "undefined" ? defaultVal : null;
}
var CACHE_DEFAULT_EXPIRATION =  86400000 // 1 day in miliseconds
var CACHE_DAY = 86400000;
var CACHE_MINUTE = 60000;
var CACHE_HOUR = 3600000;
var CACHE_WEEK = 604800000;
var CACHE_MONTH = 2592000000;
var CACHE_MONTH_31 = 2678400000;

function cacheStore(key, value, expiration){
    storagePut(key, value);
    storagePut(key + "-expiration-time", issetDefault(expiration, Date.now() + expiration));
}
function cacheGet(key){
    var data = storageGet(key);
    var expiration = storageGet(key + "-expiration-time");
    if(isset(data) && isset(expiration) ){
        if(Date.now() < expiration)
            return data;
        else{
            storagePut(key, undefined);
            storagePut(key + "-expiration-time", undefined);
        }
    }
    return null
    
}
function cacheDelete(key){
    storagePut(key, undefined);
    storagePut(key + "-expiration-time", undefined);
}




/**
 * Javascript HashCode v1.0.0
 * This function returns a hash code (MD5) based on the argument object.
 * http://pmav.eu/stuff/javascript-hash-code
 *
 * Example:
 *  var s = "my String";
 *  alert(HashCode.value(s));
 *
 * pmav, 2010
 */
var HashCode = function() {

    var serialize = function(object) {
        // Private
        var type, serializedCode = "";

        type = typeof object;

        if (type === 'object') {
            var element;

            for (element in object) {
                serializedCode += "[" + type + ":" + element + serialize(object[element]) + "]";
            }

        } else if (type === 'function') {
            serializedCode += "[" + type + ":" + object.toString() + "]";
        } else {
            serializedCode += "[" + type + ":" + object+"]";
        }

        return serializedCode.replace(/\s/g, "");
    };

    // Public, API
    return {
        value : function(object) {
            return MD5(serialize(object));
        }
    };
}();


function storagePut(key,value) {
    if (typeof(localStorage) == 'undefined' ||  $.browser.mozilla && parseInt($.browser.version,10) < 8) {
        //crap! no localstorage... Cookie fallback!
        console.log("Local storage is not available, falling back to cookies...");
        cookieSet(key, value);
        return true;
    } else {
        try {
            localStorage.setItem(key, value); //saves to the database, “key”, “value”
            return true;
        } catch (e) {
            if (e == QUOTA_EXCEEDED_ERR) {
                console.log("Local storage seems to be full (exceeded quota), falling back to cookies...");
                cookieSet(key, value)
            }
        }
    }  
    return false;
}
function storageGet(key) {
    var result = null
     
    if (typeof(localStorage) == 'undefined' ||  $.browser.mozilla && parseInt($.browser.version.slice(0,1),10) < 8 ) {
        //crap! no localstorage... Cookie fallback!
        console.log("Local storage is not available, falling back to cookies... Beware the 2 Kilobyte limit per entry.");
        result = cookieGet(key);
    } else {
        try {
            result = localStorage.getItem(key);
        } catch (e) {
            console.log("Error retrieving the entry '" + key + "' from localstore.");
        }
    } 
    return result;
}
function storageRemove(key){
    if (typeof(localStorage) == 'undefined' ) {
        //crap! no localstorage... Cookie fallback!
        console.log("Local storage is not available, nothing to remove.");
    } else {
        try {
            localStorage.removeItem(key);
        } catch (e) {
        //who cares?
        }
    } 
}
function cookieSet(name, value, days){
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime()+(days*24*60*60*1000));
        expires = "; expires="+date.toGMTString();
    }
    document.cookie = name+"="+value+expires+"; path=/";
}
function cookieGet(name){
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for(var i=0;i < ca.length;i++) {
        var c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}


function deleteCookie(name) {
    storagePut(name,"",-1);
}

function randomString(length) {
    var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');
    
    if (! length) {
        length = Math.floor(Math.random() * chars.length);
    }
    
    var str = '';
    for (var i = 0; i < length; i++) {
        str += chars[Math.floor(Math.random() * chars.length)];
    }
    return str;
}
