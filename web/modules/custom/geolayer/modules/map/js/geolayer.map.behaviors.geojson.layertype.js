(function () {
  'use strict';

  nfa.map.behaviors.geojsonLayerType = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const layerTypes = drupalSettings.geolayer_map[instance.target].layer_types;
      for (let i = 0; i < layerTypes.length; i++) {
        var url = new URL(layerTypes[i]['url'], window.location.origin + drupalSettings.path.baseUrl)
        // Pass the filters.
        const filters = drupalSettings.geolayer_map[instance.target].filters || {};
        Object.entries(filters).forEach( ([key, value]) => {
          if (Array.isArray(value)) {
            for (let i = 0; i < value.length; i++) {
              url.searchParams.append(key + '[]', value[i]);
            }
          }
          else {
            url.searchParams.append(key, value);
          }
        });

        var newLayer = instance.addLayer('geojson', {
          title: layerTypes[i]['label'] ?? Drupal.t('Layer'),
          url,
          group: layerTypes[i]['group'],
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
