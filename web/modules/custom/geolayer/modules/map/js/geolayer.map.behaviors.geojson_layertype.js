(function () {
  'use strict';

  nfa.map.behaviors.geojsonLayerType = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const layerTypes = drupalSettings.geolayer_map[instance.target].layer_types;
      for (let i = 0; i < layerTypes.length; i++) {
        var url = new URL(`geolayer/geojson/layertype/` + layerTypes[i], window.location.origin + drupalSettings.path.baseUrl)

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
        await fetch(url, {
          method: 'GET',
          headers: {
            'Content-Type': 'application/json',
          },
        }).then(function (response) {
          response.json().then(function (data) {
            data.features.forEach(function (feature) {
              // Add the layer into the group and hide (combine) child layers.
              const layer = instance.addLayer('geojson', {
                title: feature.properties.label,
                geojson: feature,
                group: feature.properties.name,
                combine: true,
                styleFunction: layerStyle,
              });
              if (layerTypes.length === 1) {
                instance.zoomToLayer(layer);
              }
            });
          });
        });
      }
      instance.zoomToVectors();
    },
    weight: 100,
  };
}());
