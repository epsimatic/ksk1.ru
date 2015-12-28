/**
 * Created by coder on 15.12.15.
 */

// Периоды обновления панелей в секундах
const timerMain=27;
const timerSidebar=42;
const timerClock=10;
const timerNowPlaying=15;
const timerWeather=60*20;

// Обновить часы
function updateClock() {
    var currentTime = new Date ( );
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );

    const nameMonth = [ "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря" ];

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentHours   = ( currentHours   < 10 ? "0" : "" ) + currentHours;

    // Update the time display
    jQuery("#clock").text(currentHours + ":" + currentMinutes);
    jQuery("#date").text(currentTime.getDate() + " " + nameMonth[currentTime.getMonth()]);
}

// Получить текущий трек
function GetNowPlaying() {
    jQuery.get("http://ksk1.ru/nowplaying.xml", function (data) {
        var track = jQuery(data).find("TRACK").first();
        if (track.attr("ARTIST")) {
            var track_text = track.attr("ARTIST") + " — " + track.attr("TITLE");
        }
        else if (track.attr("TITLE")) {
            track_text = track.attr("TITLE");
        } else  track_text = "";
        jQuery(".track-data-text").html(track_text.replace(/\[.*\]/, ""));

    });
}

function GetMain(){    jQuery(".board-main").load("http://ksk1.ru/yummies/ksk1.ru/main/"); }
function GetSidebar(){ jQuery(".board-yummie").load("http://ksk1.ru/yummies/ksk1.ru/side/"); }
function GetWeather(){ jQuery(".board-weather").load("http://ksk1.ru/weather/conditions.html"); }

jQuery(document).ready(function() {

    GetMain();
    setInterval( GetMain, timerMain * 1000 );

    GetSidebar();
    setInterval( GetSidebar, timerSidebar * 1000 );

    updateClock();
    setInterval( updateClock, timerClock * 1000 );

    GetNowPlaying();
    setInterval( GetNowPlaying, timerNowPlaying * 1000 );

    GetWeather();
    setInterval( GetWeather, timerWeather * 1000 );

    // Запускает бегущую строку  http://jonmifsud.com/open-source/jquery/jquery-webticker/
    jQuery('#webticker').webTicker({
        speed: 150,
        rssurl: 'http://brief.kskmedia.ru/feed/',
        rssfrequency: 5, // minutes
        hoverpause: false
    });

});
