(function () {
  nfa.map.behaviors.wkt = {
    attach: async function (instance) {
      function lineStyle(feature, resolution, style) {
        var layerStyle = drupalSettings.geolayer_map[instance.target].layer_style;
        switch (layerStyle.line_style) {
          case 'dotted':
            lineDash = [2, 10];
            lineCap = 'round';
            lineWidth = layerStyle.line_width;
            break;

          case 'dashed':
            lineDash = [10, 10];
            lineCap = 'square';
            lineWidth = layerStyle.line_width;
            break;

          default:
            lineDash = null;
            lineCap = null;
            lineWidth = 2;
            break
        }
        return new style.Style({
          stroke: new style.Stroke({
            color: layerStyle.color ? layerStyle.color : 'orange',
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

      // adding single layer
      if (drupalSettings.geolayer_map[instance.target].wkt) {
        var wkt = drupalSettings.geolayer_map[instance.target].wkt;
        var type = 'vector';
        var opts = {
          title: 'Geometry',
        };
        if (wkt !== '' && wkt !== 'GEOMETRYCOLLECTION EMPTY') {
          type = 'wkt';
          opts.wkt = wkt;
          opts.styleFunction = lineStyle;
        }
        var layer = instance.addLayer(type, opts);
      }
      // If edit is true, enable drawing controls.
      if (drupalSettings.geolayer_map[instance.target].behaviors.wkt.edit) {
        if (layer !== undefined) {
          instance.addBehavior('edit', { layer: layer });
        } else {
          instance.addBehavior('edit');
          var layer = instance.edit.layer;
        }
        // Add the snappingGrid behavior.
        instance.addBehavior('snappingGrid');
      }
      // Enable the line/polygon measure behavior.
      instance.addBehavior('measure', { layer: layer });
      // If the layer has features, zoom to them.
      // Otherwise, zoom to all vectors.
      if (layer !== undefined) {
        instance.zoomToLayer(layer);
      } else {
        instance.zoomToVectors();
      }
    },
    weight: 100,
  };
}());
