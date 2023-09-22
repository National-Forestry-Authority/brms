(function () {
  'use strict';

  nfa.map.behaviors.geojson = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style, feature.getProperties())
      }

      const geoLayers = drupalSettings.geolayer_map[instance.target].geolayers;
      for (let i = 0; i < geoLayers.length; i++) {
        var url = new URL("geolayer/geojson/" + geoLayers[i], window.location.origin + drupalSettings.path.baseUrl)
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
                  title: feature.properties.name,
                  fold: 'close',
                  group: feature.properties.geometry_type == 'survey' ? 'Survey layers' : 'Geometry layers'
                }
                const layerGroup = instance.addLayer('group', groupOpts);
                groups.push(feature.properties.name);
              }
              const layer = instance.addLayer('geojson', {
                title: feature.properties.name,
                geojson: feature,
                group: feature.properties.name,
                fold: 'close',
                styleFunction: layerStyle,
              });
              if (geoLayers.length === 1) {
                instance.zoomToLayer(layer);
              }
            });
          });
        });
      }
      instance.zoomToVectors();
      if (geoLayers.length > 1) {
        // Zoom out a little when there are multiple layers.
        instance.map.getView().setZoom((instance.map.getView().getZoom() - 1.5));
      }
    },
    weight: 100,
  };
}());
