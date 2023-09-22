(function () {
  'use strict';

  nfa.map.behaviors.geojsonLayerType = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const layerTypes = drupalSettings.geolayer_map[instance.target].layer_types;
      for (let i = 0; i < layerTypes.length; i++) {
        if (layerTypes[i][1] == 'survey') {
          var url_part = 'survey-geojson';
        }
        else {
          var url_part = 'geojson';
        }
        var url = new URL(`geolayer/${url_part}/layertype/${layerTypes[i][0]}`, window.location.origin + drupalSettings.path.baseUrl)
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
            // Prevent duplicate child groups by keeping track of the groups
            // we have created.
            const groups = [];
            data.features.forEach(function (feature) {
              // There are two parent layer groups: survey layers and other
              // layers. Add layer type child groups to the parent groups.
              if (!groups.includes(feature.properties.name)) {
                const groupOpts = {
                  title: feature.properties.name, // required
                  fold: 'close',
                  group: feature.properties.geometry_type == 'survey' ? 'Survey layers' : 'Geometry layers'
                }
                const layerGroup = instance.addLayer('group', groupOpts);
                groups.push(feature.properties.name);
              }
              const layer = instance.addLayer('geojson', {
                title: feature.properties.label,
                geojson: feature,
                group: feature.properties.name,
                fold: 'close',
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
