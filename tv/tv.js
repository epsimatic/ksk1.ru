/**
 * Created by coder on 15.12.15.
 */
var timerClock=10000;
var timerTrack=15000;
var timerWeather=60000*20; // 20 минут
var timerSide=15000;
function updateClock ( )
{
    var currentTime = new Date ( );
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );

    var nameMonth = [
         "января",
         "февраля",
         "марта",
         "апреля",
         "мая",
         "июня",
         "июля",
         "августа",
         "сентября",
         "октября",
         "ноября",
         "декабря"
    ];

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentHours   = ( currentHours   < 10 ? "0" : "" ) + currentHours;

    // Update the time display
    jQuery("#clock").text(currentHours + ":" + currentMinutes);
    jQuery("#date").text(currentTime.getDate() + " " + nameMonth[currentTime.getMonth()]);
}

// Получить текущий трек
function GetTextTrack(){
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
function GetWeather(){
    jQuery(".board-weather").load("http://ksk1.ru/weather/conditions.html");
}
function GetSide(){
    jQuery(".board-yummie").load("http://ksk1.ru/yummies/ksk1.ru/side/");
}
jQuery(document).ready(function(){
    updateClock();
    GetTextTrack();
    GetWeather();
    GetSide();
    setInterval('updateClock()', timerClock );
    setInterval('GetTextTrack()', timerTrack );
    setInterval('GetWeather()', timerWeather );
    setInterval('GetSide()', timerSide );
});

