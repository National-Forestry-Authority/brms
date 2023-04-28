(function () {
  nfa.map.behaviors.geojson = {
    attach: async function (instance) {
      const geoLayers = drupalSettings.geolayer_map[instance.target].geolayers;
        for (let i = 0; i < geoLayers.length; i++) {
          var url = new URL("geolayer/geojson/" + geoLayers[i], window.location.origin + drupalSettings.path.baseUrl)
          await fetch(url, {
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
            },
          }).then(function(response) {
            response.json().then(function(data) {
              data.features.forEach(function(feature) {
                const layer = instance.addLayer('geojson', {
                  title: feature.properties.label,
                  geojson: feature,
                });
                if (geoLayers.length == 1) {
                  instance.zoomToLayer(layer);
                }
              });
            });
          });
        }
        instance.zoomToVectors();
        if(geoLayers.length > 1) {
          instance.map.getView().setZoom((instance.map.getView().getZoom() - 1.5));
        }
    },
    weight: 100,
  };
}());