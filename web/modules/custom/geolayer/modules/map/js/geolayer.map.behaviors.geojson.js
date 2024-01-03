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
        var newLayer = instance.addLayer('geojson', {
          title: drupalSettings.geolayer_map[instance.target].layer_name ?? Drupal.t('Layer'),
          url,
          styleFunction: layerStyle,
        })
        var source = newLayer.getSource()
        source.on('change', function () {
          instance.zoomToVectors()
        })
      }
      instance.zoomToVectors();
    },
    weight: 100,
  };
}());
