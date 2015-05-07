/**
 * Основные функции общего шаблона
 */

// Поисковая строка
(function () {
    var meta_generator = jQuery("meta[name='generator']").attr("content");
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
            var searchbox_lazy = jQuery(this);
            LoadJS('//www.google.com/cse/cse.js?cx=' + cx, function () {
                window.setTimeout(function () {
                    searchbox_lazy.remove();
                }, 500);
            });
        });
        //TODO: Уничтожить поиск Google, он весит больше мегабайта
    }
})();

var map;
// Подгоняем высоту карты
function setMapHeight() {
    //FIXME: Не устанавливать высоту для мобильных устройств!
    jQuery('.navpanel-info > .subpanel').not('.panel-map').each(function () {
        var this_height = jQuery(this).height();
        var panel_map = jQuery('.panel-map');
        if (this_height > panel_map.height()) {
            var panel_map_map = jQuery('#panel-map');
            panel_map_map.css('height', this_height - (panel_map_map.offset().top - panel_map.offset().top));
        }
    });
    if (map) { // Уведомить leaflet, что высота поменялась. Несколько раз (костыль)
        window.setTimeout(map.invalidateSize, 100);
        window.setTimeout(map.invalidateSize, 500);
        window.setTimeout(map.invalidateSize, 1000);
    }
}


// Кнопки НавПанели и выдвижные панельки
jQuery('.triggers-weather').click(jQuery('#btn-feature-info').click());

jQuery('.btn-feature').click(function () {
    var was_active = jQuery(this).hasClass('active');
    var potential_cond_active = jQuery('.potential-cond-active');
    jQuery('.navpanel, .btn-feature').removeClass('active');
    potential_cond_active.removeClass('cond-active');
    if (!was_active) {
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

jQuery('#navpanel-info').on('first-load', function () {

    // загружаем кнопку категории вместо кнопки другие категории
    jQuery("#category-other").load("http://ksk1.ru/cat-menu.html");

    // Загружаем афишу
    jQuery("#panel-agenda").load("http://news.kskmedia.ru/agenda-block/", function () {
        setMapHeight();
        jQuery('.movie-poster').each(function() {
            jQuery(this).popover({
                content: jQuery('#content-' + jQuery(this)[0].id).html(),
                html: true,
                placement: "bottom",
                trigger: 'hover',
                container: '.today-movies-margin',
                viewport: 'body'
            });
        });
    });

// Загружаем погоду
    jQuery('#weather-panel').html('\
        <div class="popover-source-gismeteo" title="Погода от Гисметео">\
            <div id="gsInformerID-5yW5CsFg4TLIH3" class="gsInformer" style="width:300px;height:157px">\
                <div class="gsIContent">\
                    <div id="cityLink">\
                        <a href="http://www.gismeteo.ru/city/daily/4515/" target="_blank">Погода в Красноуфимске</a>\
                    </div>\
                    <div class="gsLinks"><table><tr><td>\
                        <div class="leftCol">\
                            <a href="http://www.gismeteo.ru" target="_blank">\
                                <img alt="Gismeteo" title="Gismeteo" src="" align="absmiddle" border="0"/>\
                                <span>Gismeteo</span>\
                            </a>\
                        </div>\
                        <div class="rightCol">\
                            <a href="http://www.gismeteo.ru/city/weekly/4515/" target="_blank">Прогноз на 2 недели</a>\
                        </div>\
                    </td></tr></table></div>\
                </div>\
            </div>\
        </div>');
    LoadCSS('http://www.gismeteo.ru/static/css/informer2/gs_informerClient.min.css');
    LoadJS('http://www.gismeteo.ru/ajax/getInformer/?hash=5yW5CsFg4TLIH3', function () {
        setMapHeight();
    });

// Загружаем карту
    setMapHeight();
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

        LoadJS('http://ksk1.ru/vendor/leaflet-geosearch/src/js/l.control.geosearch.js', function () {
            L.GeoSearch.Provider.OpenStreetMapKsk = L.Class.extend({
                initialize: function (options) {
                    //noinspection JSUnusedAssignment
                    options = L.Util.setOptions(this, options);
                },
                /**
                 * @return {string} Service URL
                 */
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
    });

// Отключаемся от события
    jQuery(this).unbind('first-load');
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
var offset = 1000; // px from page top
if (jQuery('.btn-scroll-up').length) {
    // У нас уже есть кнопка «Наверх», ничего делать не надо
} else {
    var btn_home = jQuery('<a/>', {
        href: '#header',
        class: 'btn-home inactive text-center',
        title: 'К верху страницы',
        html: '<i class="fa fa-angle-up fa-5x"></i> <div>НАВЕРХ</div>'
    }).click(function(event){
        jQuery('html,body').animate({ scrollTop: 0 }, 'slow');
        btn_home.blur();
        event.preventDefault();
    }).appendTo('footer.hidden-print');

    jQuery(window).scroll(function () {
        if (jQuery(this).scrollTop() > offset)
            btn_home.removeClass("inactive");
        else
            btn_home.addClass("inactive");
    });
}



// Browser-update
var $buoop = { text: "Ваш браузер (%s) <b>устарел</b>. Он <b>небезопасен</b> и <b>не показывает все возможности</b> этого и других сайтов. \
<a%s>Узнайте, как обновить ваш браузер</a>" };
LoadJS('//browser-update.org/update.js');
