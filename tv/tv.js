/**
 * Created by coder on 15.12.15.
 */
function updateClock ( )
{
    var currentTime = new Date ( );
    var currentMonth = currentTime.getMonth();
    var currentDay   = currentTime.getDate();
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );
    var nameMonth = {0: "января",
         1: "февраля",
         2: "марта",
         3: "апреля",
         4: "мая",
         5: "июня",
         6: "июля",
         7: "августа",
         8: "сентября",
         9: "октября",
         10: "ноября",
         11: "декабря"
    };

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

    // Choose either "AM" or "PM" as appropriate
    /*var timeOfDay = ( currentHours < 12 ) ? "AM" : "PM";*/

    // Convert the hours component to 12-hour format if needed
  /*  currentHours = ( currentHours > 12 ) ? currentHours - 12 : currentHours;*/

    // Convert an hours component of "0" to "12"
    currentHours = ( currentHours == 0 ) ? 12 : currentHours;

    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes ;
    var currentDateString = currentDay + " " + nameMonth[currentMonth] ;

    // Update the time display
    jQuery("#clock").text(currentTimeString);
    jQuery("#date").text(currentDateString);
}

jQuery(document).ready(function(){
    updateClock();
    setInterval('updateClock()', 10000 );
});

