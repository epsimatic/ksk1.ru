/**
 * Created by coder on 15.12.15.
 */

const timers = { // Периоды обновления панелей в секундах
    main: 30,
    sidebar: 42,
    clock: 10,
    now_playing: 15,
    weather: 20*60,
    ticker: 15*60
}

/**
 * Обновить часы в селектор
 * @param {string} selector jQuery selector for 2 elements
 */
function updateClock(selector) {
    var currentTime = new Date ( );
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );

    const nameMonth = [ "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря" ];

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentHours   = ( currentHours   < 10 ? "0" : "" ) + currentHours;

    // Update the time display
    jQuery(selector).first().text(currentTime.getDate() + " " + nameMonth[currentTime.getMonth()]);
    jQuery(selector).last().text(currentHours + ":" + currentMinutes);
}


/**
 * Получить текущий трек в селектор
 * @param {string} selector jQuery selector
 */
function GetNowPlaying(selector) {
    jQuery.get("http://ksk1.ru/nowplaying.xml", function (data) {
        var track = jQuery(data).find("TRACK").first();
        if (track.attr("ARTIST")) {
            var track_text = track.attr("ARTIST") + " — " + track.attr("TITLE");
        }
        else if (track.attr("TITLE")) {
            track_text = track.attr("TITLE");
        } else  track_text = "";
        jQuery(selector).html(track_text.replace(/\[.*\]/, ""));
    });
}

function UpdateBlockUpdateTimer ( selector, url_or_function, seconds ) {
    if (typeof (url_or_function) == 'function') {
        url_or_function (selector);
    } else {
        jQuery(selector).load(url_or_function);
    }
    setTimeout( function(){ UpdateBlockUpdateTimer(selector, url_or_function, seconds); }, seconds * 1000 );
}

//LoadJS("http://ksk1.ru/bootstrap-3c/js/bootstrap.min.js");

LoadJS("http://ksk1.ru/js/jquery-1.js", function(){
    jQuery(document).ready(function() {

        UpdateBlockUpdateTimer(".board-main", "http://ksk1.ru/yummies/ksk1.ru/main/", timers['main']);
        UpdateBlockUpdateTimer(".board-yummie", "http://ksk1.ru/yummies/ksk1.ru/side/", timers['sidebar']);
        UpdateBlockUpdateTimer(".board-weather", "http://ksk1.ru/weather/conditions.html", timers['weather']);
        UpdateBlockUpdateTimer(".track-data-text", GetNowPlaying, timers['now_playing']);
        UpdateBlockUpdateTimer("#clock, #date", updateClock, timers['clock']);

        // Запускает бегущую строку  http://jonmifsud.com/open-source/jquery/jquery-webticker/
        LoadJS('/tv/news-ticker.js', function(){
            jQuery('#webticker').webTicker({
                speed: 150,
                rssurl: 'http://brief.kskmedia.ru/feed/',
                rssfrequency: timers['ticker'] / 60,
                hoverpause: false
            });
        });
    });
});

window.libsAvail   = [];
window.libsLoading = [];
function LoadRes(src, type, callback) {
    var resName = src.split("/").reverse()[0];
    if (libsAvail.indexOf(resName) != -1) {
        console.log("Available already: «" + resName + "» " + src +
            ((typeof (callback) == "function") ? ", running callback" : ""));
        if (typeof (callback) == "function") callback();
    } else if (libsLoading.indexOf(resName) != -1) {
        console.log("Still loading, retry: «" + resName + "» " + src);
        window.setTimeout( function() { LoadRes(src, type, callback) }, 100);
    } else {
        window.libsLoading.push(resName);
        console.log("Loading «" + resName + "» " + src);
        var e = document.createElement(type);
        if (type == 'script') {
            e.type = "text/javascript";
            e.src = src;
            e.async = true;
        } else {
            e.type = "text/css";
            e.href = src;
            e.rel = "stylesheet";
        }
        e.onerror = function () {
            console.error("Error loading " + src);
            window.libsLoading.splice(window.libsLoading.indexOf(resName),1);
        };
        e.onload = function () {
            resName = src.split("/").reverse()[0];
            console.log("Loaded «" + resName + "» " + src +
                ((typeof (callback) == "function") ? ", running callback" : ""));
            window.libsAvail.push(resName);
            window.libsLoading.splice(window.libsLoading.indexOf(resName),1);
            if (typeof (callback) == "function") callback();
        };
        document.getElementsByTagName("head")[0].appendChild(e);
    }
}
function LoadJS (src, onload) {LoadRes(src, 'script', onload)}
function LoadCSS(src, onload) {LoadRes(src, 'link', onload)}