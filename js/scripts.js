/**
 * Created by coder on 28.07.14.
 */
function setCookie(key, value, time ) {
    var expires = new Date();
    expires.setTime(expires.getTime() + time);
    /* 2'600'000'000 is 1 month */
    document.cookie = key + '=' + escape(value || '') + ';expires=' + expires.toUTCString();
}
function getCookie(key) { var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? unescape(keyValue[2]) : null; }