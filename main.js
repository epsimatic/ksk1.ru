/**
 * Основные функции общего шаблона
 */

// Поисковая строка
(function () {
    const meta_generator = jQuery("meta[name='generator']").attr("content");
    if (meta_generator && meta_generator.substring(0, 9) == "WordPress") {
        // Пусто. У сайтов на WordPress свой поиск
    } else {
        if (document.location.href.match(/^[http:\/\/]+ob/)) { // Поиск по объявлениям
            var cx = '003704283744183876190:qchgmzsmjkc';
        } else { // Поиск по новостям
            cx = '003704283744183876190:woiuqgnl_eg';
        }
        jQuery('.searchbox').append('<div id="searchbox-lazy"><div class="form-group clearfix"><i class="fa fa-search"></i><input type="text" placeholder="Поиск..." class="form-control" size="40"></div></div>');
        jQuery('#searchbox-lazy').hover(function () {
            const searchbox_lazy = jQuery(this);
            LoadJS('//www.google.com/cse/cse.js?cx=' + cx, function () {
                window.setTimeout(function () {
                    searchbox_lazy.remove();
                }, 500);
            });
        });
        // по щелчку кнопки поиска
        jQuery(".btn-search").click(function(){
            jQuery(".searchbox-container").addClass("search-show");

            LoadJS('//www.google.com/cse/cse.js?cx=' + cx);

        });
        jQuery(".close").click(function(){
            jQuery(".searchbox-container").removeClass("search-show");
        });
        //TODO: Уничтожить поиск Google, он весит больше мегабайта
    }
})();


var map, layersControl;
// Подгоняем высоту карты
function setMapHeight() {
    //FIXME: ОТКЛЮЧЕНО!
    return;

    //FIXME: Не устанавливать высоту для мобильных устройств!
    jQuery('.navpanel-info > .subpanel').not('.panel-map').each(function () {
        const this_height = jQuery(this).height();
        const panel_map = jQuery('.panel-map');
        if (this_height > panel_map.height()) {
            const panel_map_map = jQuery('#panel-map');
            panel_map_map.css('height',
                this_height - (panel_map_map.offset().top - panel_map.offset().top));
        }
    });
    if (map) { // Уведомить leaflet, что высота поменялась. Несколько раз (костыль)
        map.invalidateSize();
        window.setTimeout(map.invalidateSize, 300);
        window.setTimeout(map.invalidateSize, 800);
    }
}
// кнопкa "Карта" на панели в Городе
//jQuery('.map-feature').click( function() {jQuery('#btn-feature-services').click();});

// Кнопки НавПанели и выдвижные панельки
jQuery('.triggers-weather').click( function() {jQuery('#btn-feature-info').click();
    jQuery("#panel-movies").appendTo(".panel_movie_weather");} );

jQuery('.btn-feature').not('.cat-feature , .map-feature').click(function () {
    const was_active = jQuery(this).hasClass('active'),
          potential_cond_active = jQuery('.potential-cond-active');
    jQuery('.navpanel, .btn-feature').removeClass('active');
    potential_cond_active.removeClass('cond-active');
    if (!was_active) {
       if (jQuery(this).attr("id")=="btn-feature-info"){  // панель с погодой внизу
           jQuery("#weather-panel").appendTo(".panel_movie_weather");
       }
        jQuery(this).addClass('active');
        jQuery('.' + jQuery(this)[0].id.replace('btn-feature', 'navpanel'))
            .removeClass('hidden').addClass('active').trigger('first-load');
    } else {
        potential_cond_active.addClass('cond-active');
    }
});

jQuery('.btn-collapse').click(function () {
    jQuery('.navpanel, .btn-feature').removeClass('active');
    jQuery('.potential-cond-active').addClass('cond-active');
});

jQuery('#navpanel-info').one('first-load', function () {
    // загружаем кнопку категории вместо кнопки другие категории
    jQuery("#category-other").load("http://ksk1.ru/cat-menu.html");
    var tmp = new Date();
    var t=tmp.getDay();
    var week=['ВС','ПН','ВТ','СР','ЧТ','ПТ','СБ'];
    jQuery('.list-days-ajax [data-toggle]').each(function () {
        var res = jQuery(this).attr('data-day');
        if (res>2) {
            t=new Date();
            n=new Date(t.setDate(t.getDate() + parseInt(res-1) ) );
            jQuery(this).text(week[n.getDay()]);
        }

    });

    jQuery('.list-days-ajax a[data-toggle="tab"]').one('shown.bs.tab', function () {

        var day_num = jQuery(this).data('day');
        console.log('день = '+day_num);
        jQuery("#day"+day_num).load("http://news.kskmedia.ru/movies-block/",{"day_week":day_num}, function() {
            if (day_num > 1 && day_num < 7)
                jQuery('.list-days-ajax a[data-day="'+(day_num+1)+'"]').trigger('shown.bs.tab');
        });
    });

    jQuery('.list-days-ajax a[href="#day1"]').trigger('shown.bs.tab'); //загрузка закладки "Сегодня в кино"


    // Загружаем афишу
    jQuery("#panel-agenda").load("http://news.kskmedia.ru/agenda-block/", setMapHeight);

});

function AddGeosearch() {
    LoadJS('http://ksk1.ru/vendor/leaflet-geosearch/src/js/l.control.geosearch.js', function () {
        L.GeoSearch.Provider.OpenStreetMapKsk = L.Class.extend({
            initialize: function (options) {
                options = L.Util.setOptions(this, options);
            },
            /** @return {string} Service URL */
            GetServiceUrl: function (qry) {
                var parameters = L.Util.extend({q: "Красноуфимск " + qry, format: 'json'}, this.options);
                return 'http://nominatim.openstreetmap.org/search' + L.Util.getParamString(parameters);
            },
            ParseJSON: function (data) {
                if (data.length == 0) return [];
                var results = [];
                for (var i = 0; i < data.length; i++)
                    results.push(new L.GeoSearch.Result(data[i].lon, data[i].lat, data[i].display_name));
                return results;
            }
        });

        new L.Control.GeoSearch({
            provider: new L.GeoSearch.Provider.OpenStreetMapKsk(),
            country: 'ru',
            searchLabel: 'Найти на карте…',
            notFoundMessage: 'К сожалению, ничего не найдено',
            zoomLevel: 17
        }).addTo(map);
    });
}
function AddLayerGoogle() {
    var tiles_OpenMapSurfer_hybrid = L.tileLayer('http://korona.geog.uni-heidelberg.de/tiles/hybrid/x={x}&y={y}&z={z}', {
        minZoom: 8,
        maxZoom: 20,
        attribution: 'Карта: <a href="http://openstreetmap.org">OpenStreetMap</a>, ' +
        'плитки: <a href="http://giscience.uni-hd.de/">GIScience</a>'
    });
    LoadJS('http://ksk1.ru/vendor/leaflet-plugins/layer/tile/Google.js', function () {
        layersControl.addBaseLayer( L.layerGroup( [new L.Google(), tiles_OpenMapSurfer_hybrid] ), 'Спутник Google' );
    });
}

function AddLayerBing() {
    var tiles_OpenMapSurfer_hybrid = L.tileLayer('http://korona.geog.uni-heidelberg.de/tiles/hybrid/x={x}&y={y}&z={z}', {
        minZoom: 8,
        maxZoom: 20,
        attribution: 'Карта: <a href="http://openstreetmap.org">OpenStreetMap</a>, ' +
        'плитки: <a href="http://giscience.uni-hd.de/">GIScience</a>'
    });

    LoadJS('http://ksk1.ru/vendor/leaflet-plugins/layer/tile/Bing.js', function () {
        layersControl.addBaseLayer( L.layerGroup( [ new L.BingLayer("AqYQy-mMupdP9Y5Ig8rx374e1-Rai_sBWOwD_FuUDp9b1exLtRRbMYxIcTmGZe2Z"),
            tiles_OpenMapSurfer_hybrid] ), "Спутник Bing" );
    });
}

function AddLayerYandex(){
    LoadJS('http://api-maps.yandex.ru/2.0/?load=package.map&lang=ru-RU', function () {
        LoadJS('http://ksk1.ru/vendor/leaflet-plugins/layer/tile/Yandex.js', function () {
            layersControl.addBaseLayer(new L.Yandex('map'), "Карта Яндекс" );
        });
    });
}

function AddOverlayHills() {
    var tiles_OpenMapSurfer_hills_hybrid = L.tileLayer('http://129.206.74.245:8004/tms_hs.ashx?x={x}&y={y}&z={z}', {
        opacity: 0.3
//        minZoom: 8,
//        maxZoom: 20
// TODO: attribution http://korona.geog.uni-heidelberg.de/contact.html
    });

    layersControl.addOverlay(tiles_OpenMapSurfer_hills_hybrid, "Рельеф")
}

function AddButtonFullScreen(){
    LoadCSS('http://ksk1.ru/vendor/leaflet-fullscreen-brunob/Control.FullScreen.css');
    LoadJS( 'http://ksk1.ru/vendor/leaflet-fullscreen-brunob/Control.FullScreen.js', function () {
        L.control.fullscreen({
            position: 'topleft',
            title: 'Развернуть на весь экран',
            content: "<i class='fa fa-expand'></i>",
            forceSeparateButton: true
        }).addTo(map);

    });
}
function AddControlLoading(){
    LoadCSS('http://ksk1.ru/vendor/leaflet-loading/src/Control.Loading.css');
    LoadJS( 'http://ksk1.ru/vendor/leaflet-loading/src/Control.Loading.js', function () {
        var loadingControl = L.Control.loading({
            separate: true
        });
        map.addControl(loadingControl);
    });
}
function AddButtonHome(){
    LoadCSS('http://ksk1.ru/vendor/leaflet-defaultextent/dist/leaflet.defaultextent.css');
    LoadJS( 'http://ksk1.ru/vendor/leaflet-defaultextent/dist/leaflet.defaultextent.js', function () {
        L.control.defaultExtent({title: 'Возврат к первоначальному виду'}).addTo(map);
    });
}
function AddButtonLocate(){
    LoadCSS('http://ksk1.ru/vendor/leaflet-locatecontrol/dist/L.Control.Locate.min.css');
    LoadJS( 'http://ksk1.ru/vendor/leaflet-locatecontrol/dist/L.Control.Locate.min.js', function () {
        L.control.locate({strings:{title: "Где я нахожусь"}}).addTo(map);
    });
}
function AddRoutingMachine(){
    LoadCSS('http://ksk1.ru/vendor/leaflet-routing-machine/dist/leaflet-routing-machine.css');
    LoadJS( 'http://ksk1.ru/vendor/leaflet-routing-machine/dist/leaflet-routing-machine.js', function () {
        L.Routing.control({
            waypoints: [
                L.latLng(map.getCenter().lat,map.getBounds().getEast()*.25 + map.getBounds().getWest()*.75 ),
                L.latLng(map.getCenter().lat,map.getBounds().getEast()*.75 + map.getBounds().getWest()*.25 )
            ],
            routeWhileDragging: true
        }).addTo(map);
        jQuery(".leaflet-marker-icon").css('z-index','200');
    });
}

function AddButtonRouting() {
    LoadJS("http://ksk1.ru/vendor/leaflet-easybutton/easy-button.js",function(){
        L.easyButton('fa-exchange', function (){
                //TODO: Скрывать навигацию, если она есть (сделать кнопку-переключатель)
                //TODO: Помечать кнопку как активную (не очень нужно)
                if(jQuery('img').is('.leaflet-marker-icon')== false)
                {
                    AddRoutingMachine();
                }
                else {
                    jQuery('img.leaflet-marker-icon').remove();
                    jQuery('.leaflet-routing-container').remove();
                    jQuery('.leaflet-clickable').remove();
                }
            },
            'Проложить маршрут по карте'
        );
    });
}

function AddMeasureControl(){
    var drawnItems = new L.FeatureGroup();
    map.addLayer(tiles_OpenMapSurfer_hybrid);
    var drawControl = new L.Control.Draw({
        edit: {
            featureGroup: tiles_OpenMapSurfer_hybrid
        }
    });
    map.addControl(drawControl);
    map.on('draw:created', function (e) {
        var type = e.layerType,
            layer = e.layer;
        if (type === 'marker') {
            // Do marker specific actions
        }
        // Do whatever else you need to. (save to db, add to map etc)
        map.addLayer(layer);
    });
}
function AddMap(name_id,map_height){
    jQuery('#'+name_id).css('height',map_height);
    LoadCSS('http://ksk1.ru/vendor/leaflet/dist/leaflet.css');
    LoadCSS('http://ksk1.ru/vendor/leaflet-addon.css');
// TODO: загружать локальный leaflet
    LoadJS('http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js', function () {

        map = L.map(name_id,{drawControl: true});
        map.setView([56.6132, 57.7689], 13);
        layersControl = new L.Control.Layers(null, null, { 'collapsed': false }).addTo(map);
        var tiles_OpenMapSurfer = L.tileLayer('http://openmapsurfer.uni-hd.de/tiles/roads/x={x}&y={y}&z={z}', {
            minZoom: 8,
            maxZoom: 20,
            attribution: 'Карта: <a href="http://openstreetmap.org">OpenStreetMap</a>, ' +
            'плитки: <a href="http://giscience.uni-hd.de/">GIScience</a>'
        });
        tiles_OpenMapSurfer.addTo(map);
        layersControl.addBaseLayer( tiles_OpenMapSurfer, 'Карта OpenStreetMap');

        AddControlLoading();
        AddButtonFullScreen();
        AddButtonRouting();
        AddButtonHome();

        AddGeosearch();

        AddButtonLocate();

        LoadJS("https://raw.githubusercontent.com/vogdb/Leaflet.ActiveLayers/master/dist/leaflet.active-layers.min.js");

        window.setTimeout(function(){
            //  AddLayerESRI();
            AddLayerGoogle();
            AddLayerBing();
            AddLayerYandex();
            AddOverlayHills();
        }, 100)
    });
}

jQuery('#navpanel-services').one('first-load', function () {
// Загружаем карту
    if (typeof map ==="undefined")
    AddMap('panel-map',428);
   /* setMapHeight();
    LoadCSS('http://ksk1.ru/vendor/leaflet/dist/leaflet.css');
    LoadCSS('http://ksk1.ru/vendor/leaflet-addon.css');
// TODO: загружать локальный leaflet
    LoadJS('http://cdn.leafletjs.com/leaflet-0.7.3/leaflet.js', function () {

        map = L.map('panel-map');
        map.setView([56.6132, 57.7689], 13);
        L.tileLayer('http://openmapsurfer.uni-hd.de/tiles/roads/x={x}&y={y}&z={z}', {
            minZoom: 8,
            maxZoom: 20,
            attribution: 'Карта: <a href="http://openstreetmap.org">OpenStreetMap</a>, ' +
                         'плитки: <a href="http://giscience.uni-hd.de/">GIScience</a>'
        }).addTo(map);

        AddGeosearch();
    });*/
});

//  Openstat
var openstat = { counter: 2173092, image: 5088, color: "828282", next: openstat,
    part: jQuery('body').prop('class').split(' ')[0] };
LoadJS('//openstat.net/cnt.js');


// Yandex.Metrika counter
/*
    (w[c] = w[c] || []).push(function () {
    try {
    w.yaCounter5036764 = new Ya.Metrika({id: 5036764,
    webvisor: true,
    clickmap: true,
    trackLinks: true,
    accurateTrackBounce: true});
    } catch (e) {}
    });
    LoadJS('//mc.yandex.ru/metrika/watch.js');
*/

// noscript: <img src="//mc.yandex.ru/watch/5036764" style="position:absolute; left:-9999px;" alt=""/>

// Кнопка «Наверх»
const topOffsetToShowBtn = 1000;
if (jQuery('.btn-scroll-up').length) {
    // У нас уже есть кнопка «Наверх», ничего делать не надо
} else {
    var btn_home = jQuery('<a/>', {
        href: '#header',
        class: 'btn-home inactive text-center',
        title: 'К верху страницы',
        html: '<div><svg baseProfile="basic" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 45 70"><path fill="#999" d="M44.546 60.295l-2.251 2.251c-.273.273-.634.454-1.038.454-.362 0-.766-.181-1.039-.454l-17.718-17.718-17.718 17.718c-.273.273-.677.454-1.039.454s-.766-.181-1.039-.454l-2.251-2.251c-.272-.273-.453-.677-.453-1.038 0-.362.181-.766.454-1.039l21.007-21.008c.273-.273.677-.454 1.039-.454s.766.181 1.039.454l21.008 21.008c.272.273.453.677.453 1.039 0 .361-.181.765-.454 1.038z"/></svg></div> <div>НАВЕРХ</div>'
    }).click(function(event){
        jQuery('html,body,#content').animate({ scrollTop: 0 }, 'slow');
        btn_home.blur();
        event.preventDefault();
    });
    var url = document.location.href;
    if(url.match(/ob.ksk66/))
       btn_home.appendTo('body');
    else {btn_home.appendTo('footer.hidden-print');
      console.log("Создание кнопки");}
    jQuery(window).scroll(ShowHideBtnHome);
    jQuery('#content').scroll(ShowHideBtnHome);
}
function ShowHideBtnHome() {
    if (jQuery(this).scrollTop() > topOffsetToShowBtn)
        btn_home.removeClass("inactive");
    else
        btn_home.addClass("inactive");
}



// Browser-update
var $buoop = { text: "Ваш браузер (%s) <b>устарел</b>. Он <b>небезопасен</b> и <b>не показывает все возможности</b> этого и других сайтов. \
<a%s>Узнайте, как обновить ваш браузер</a>" };
LoadJS('//browser-update.org/update.js');


// Включаем Snap.js
var snapper = new Snap({
    element: document.getElementById('content')
    //disable: 'right'
});
jQuery('#menu-button').on('click', function() {
    if (snapper.state().state == "left") {
        snapper.close();
    } else {
        snapper.open('left');
    }
});

// Автоматическое скрытие на больших экранах
jQuery(window).resize(function() {
    if (jQuery(this).width() < 768) {
        snapper.enable();
    } else {
        snapper.close();
        snapper.disable();
    }
}).trigger('resize');

// Разворачивание меню по щелчку
jQuery('.menu-item-has-children > a').click(function(event){
    event.preventDefault();
    var thisParent = jQuery(this).parent();
    if (thisParent.hasClass('current-page-parent')) {
        thisParent.removeClass('current-page-parent');
    } else {
        thisParent.addClass('current-page-parent');
        jQuery('.menu-item-has-children > a').parent().not(thisParent).removeClass('current-page-parent');
    }
});


/*  поповер по hover
jQuery('.wide-header .popover-weather').popover({
        content: "<h1 style='padding: 50px 50px;'>Загрузка...</h1>",
        title: "Прогноз погоды на 3 дня",
//        template: '<div class="popover popover-weather-temp"><div class="arrow"></div><div class="popover-header">\
//<button type="button" class="close" aria-hidden="true">&times;</button>\
//<h3 class="popover-title"></h3></div><div class="popover-content"></div></div>',
        html: true,
        placement: "bottom",
        trigger:"hover"
    }).one('show.bs.popover', function(event){
        jQuery.ajax({
            url: "http://ob.ksk66.ru/weather/forecast.html",
            timeout: 2000,
            success: function (data) {
                // Надо обновить уже висящую подсказку и изменить options.content для новых подсказок
                var popover = jQuery('.wide-header .popover-weather').data('bs.popover');
                popover.tip().find(".popover-content").html(data);
                popover.options.content = data;
            },
            error: function(msg){
                result = msg.responseText ? msg.responseText : msg.statusText;
                    jQuery('.popover-weather + .popover > .popover-content').html("<p>Ошибка: "+result+"<br>Посмотрите на Яндекс.Погода<br> <a class='ya-weather-forecast' href='https://pogoda.yandex.ru/krasnoufimsk/details' target='_blank'><img alt='Погода' src='//info.weather.yandex.net/krasnoufimsk/2_white.ru.png?domain=ru'></a></p>");
            }

        });
    });*/

jQuery('.dropdown-weather').one('mouseenter',function(){
    jQuery.ajax({
        url: "http://ob.ksk66.ru/weather/forecast.html",
        timeout: 2000,
        success: function (data) {
            jQuery('.dropdown-weather').find('.dropdown-menu').html(data)
        },
        error: function(msg){
            result = msg.status + ' ' + msg.statusText;
            jQuery('.dropdown-weather').find('.dropdown-menu').html("<div class='alert alert-danger'>Ошибка: <b>"+result+"</b><br>Вот погода от Яндекса:</div> <a class='ya-weather-forecast' href='https://pogoda.yandex.ru/krasnoufimsk/details' target='_blank'><img alt='Погода' src='//info.weather.yandex.net/krasnoufimsk/2_white.ru.png?domain=ru'></a>");
        }

    });
});
// Форма для сбора отзывов
function showReviewHide() {
    $(".review-hide").removeClass('hidden');
    $(".review-text").focus();
}
function formFade() {
    $('.wrapp__buttonSubmit').button('loading');
    window.setTimeout(function () {
        $('#review-form').fadeTo(1000, 0);
    }, 1000);
}