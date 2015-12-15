/**
 * Created by coder on 15.12.15.
 */
function updateClock ( )
{
    var currentTime = new Date ( );
    var currentMonth = currentTime.getMonth();
    var currentDay   = currentTime.getDay();
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );

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
    var currentDateString = currentDay + " " + currentMonth ;

    // Update the time display
    document.getElementById("clock").firstChild.nodeValue = currentTimeString;
    document.getElementById("date").firstChild.nodeValue = currentDateString;
}

