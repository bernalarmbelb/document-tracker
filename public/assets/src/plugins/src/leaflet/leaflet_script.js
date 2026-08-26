/**
 * 
 *  Basic 
 */

var map = L.map('basic-map').setView([51.505, -0.09], 13);

var tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, ' +
        'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
    id: 'mapbox/streets-v11',
    tileSize: 512,
    zoomOffset: -1
}).addTo(map);

var marker = L.marker([51.5, -0.09]).addTo(map);


/**
 * 
 *  Panes
 */

var map = L.map('map-panes');

map.createPane('labels');

// This pane is above markers but below popups
map.getPane('labels').style.zIndex = 650;

// Layers in this pane are non-interactive and do not obscure mouse/touch events
map.getPane('labels').style.pointerEvents = 'none';

var cartodbAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors, &copy; <a href="https://carto.com/attribution">CARTO</a>';

var positron = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_nolabels/{z}/{x}/{y}.png', {
    attribution: cartodbAttribution
}).addTo(map);

var positronLabels = L.tileLayer('http://{s}.basemaps.cartocdn.com/light_only_labels/{z}/{x}/{y}.png', {
    attribution: cartodbAttribution,
    pane: 'labels',
    color: '#00ab55'
}).addTo(map);

/* global euCountries */
var geojson = L.geoJson(euCountries).addTo(map);

geojson.eachLayer(function (layer) {
    layer.bindPopup(layer.feature.properties.name);
});

map.setView({lat: 47.040182144806664, lng: 9.667968750000002}, 4);