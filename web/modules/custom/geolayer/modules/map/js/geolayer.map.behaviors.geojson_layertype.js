(function () {
  nfa.map.behaviors.geojsonLayerType = {
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
                styleFunction: lineStyle,
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
