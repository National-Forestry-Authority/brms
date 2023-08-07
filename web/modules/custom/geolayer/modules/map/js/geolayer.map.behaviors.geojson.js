(function () {
  nfa.map.behaviors.geojson = {
    attach: async function (instance) {
      function lineStyle(feature, resolution, style) {
        switch (feature.getProperties().line_style) {
          case 'dotted':
            lineDash = [2, 10];
            lineCap = 'round';
            lineWidth = feature.getProperties().line_width;
            break;

          case 'dashed':
            lineDash = [10, 10];
            lineCap = 'square';
            lineWidth = feature.getProperties().line_width;
            break;

          default:
            lineDash = null;
            lineCap = null;
            lineWidth = 2;
            break
        }
        return new style.Style({
          stroke: new style.Stroke({
            color: feature.getProperties().color ? feature.getProperties().color : 'orange',
            width: lineWidth,
            lineDash: lineDash,
            lineCap: lineCap,
          }),
          // Must define fill so clicks can be detected. Add a transparent fill.
          fill: new style.Fill({
            color: 'rgba(0,0,0,0)',
          }),
        });
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
                styleFunction: lineStyle,
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
