var $buoop = { text: "Ваш браузер (%s) <b>устарел</b>. В нём могут быть <b>уязвимости</b> и он <b>не показывает все возможности</b> этого и других сайтов. \
<a%s>Узнайте, как обновить ваш браузер</a>" };
function $buo_f() {
    var e = document.createElement("script");
    e.src = "//browser-update.org/update.js";
    document.body.appendChild(e);
}
try {
    document.addEventListener("DOMContentLoaded", $buo_f, false)
}
catch (e) {
    window.attachEvent("onload", $buo_f)
}
