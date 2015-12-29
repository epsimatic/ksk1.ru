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
    jQuery(selector).first().text(currentHours + ":" + currentMinutes);
    jQuery(selector).last().text(currentTime.getDate() + " " + nameMonth[currentTime.getMonth()]);
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
    setTimeout( function(){ UpdateBlockUpdateTimer(selector, url, seconds); }, seconds * 1000 );
}


jQuery(document).ready(function() {

    UpdateBlockUpdateTimer(".board-main", "http://ksk1.ru/yummies/ksk1.ru/main/", timers['main']);
    UpdateBlockUpdateTimer(".board-yummie", "http://ksk1.ru/yummies/ksk1.ru/side/", timers['sidebar']);
    UpdateBlockUpdateTimer(".board-weather", "http://ksk1.ru/weather/conditions.html", timers['weather']);
    UpdateBlockUpdateTimer(".track-data-text", GetNowPlaying, timers['now_playing']);
    UpdateBlockUpdateTimer("#clock, #date", updateClock, timers['clock']);

    // Запускает бегущую строку  http://jonmifsud.com/open-source/jquery/jquery-webticker/
    jQuery('#webticker').webTicker({
        speed: 150,
        //rssurl: 'http://brief.kskmedia.ru/feed/',
        //rssfrequency: timers['ticker'] / 60,
        hoverpause: false
    });

});
