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
            data.features.forEach(function (feature) {
              const layer = instance.addLayer('geojson', {
                title: feature.properties.name,
                geojson: feature,
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
