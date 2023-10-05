(function () {
  'use strict';

  nfa.map.behaviors.geojson = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const urls = drupalSettings.geolayer_map[instance.target].urls;
      for (let i = 0; i < urls.length; i++) {
        var url = new URL(urls[i], window.location.origin + drupalSettings.path.baseUrl)
        await fetch(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        }).then(function (response) {
          response.json().then(function (data) {
            data.features.forEach(function (feature) {
              const layer = instance.addLayer('geojson', {
                title: feature.properties.name,
                geojson: feature,
                group: feature.properties.layer_group == 'survey' ? 'Survey layers' : 'Feature layers',
                styleFunction: layerStyle,
              });
            });
          })
        });
      }
      instance.zoomToVectors();
    },
    weight: 100,
  };
}());
