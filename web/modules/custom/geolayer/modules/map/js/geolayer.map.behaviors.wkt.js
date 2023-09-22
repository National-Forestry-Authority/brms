(function () {
  nfa.map.behaviors.wkt = {
    attach: async function (instance) {
      function layerStyle(feature, resolution, style) {
        return getLayerStyle(feature, resolution, style,  drupalSettings.geolayer_map[instance.target].layer_style)
      }

      // Add a single layer.
      if (drupalSettings.geolayer_map[instance.target].wkt) {
        var wkt = drupalSettings.geolayer_map[instance.target].wkt;
        var type = 'vector';
        var opts = {
          title: 'Geometry',
        };
        if (wkt !== '' && wkt !== 'GEOMETRYCOLLECTION EMPTY') {
          type = 'wkt';
          opts.wkt = wkt;
          opts.styleFunction = layerStyle;
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
