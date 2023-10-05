(function () {
  'use strict';

  nfa.map.behaviors.geojsonBaseMaps = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const urls = drupalSettings.geolayer_map[instance.target].base_map_urls;
      for (let i = 0; i < urls.length; i++) {
        var url = new URL(urls[i]['url'], window.location.origin + drupalSettings.path.baseUrl)
        var newLayer = instance.addLayer('geojson', {
          title: urls[i]['layer_name'],
          url,
          group: 'Base maps',
          visible: false,
          styleFunction: layerStyle,
        })
        var source = newLayer.getSource()
      }

    },
    weight: 100,
  };
}());
