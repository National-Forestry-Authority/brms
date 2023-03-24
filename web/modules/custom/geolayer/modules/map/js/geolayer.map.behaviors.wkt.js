(function () {
  geolayer.map.behaviors.wkt = {
    attach: async function (instance) {
      // If WKT was set, create a layer.
      const geoLayers = drupalSettings.geolayer_map[instance.target].geolayers;
      const mapType = drupalSettings.geolayer_map[instance.target].map_type;
      if(mapType == 'geolayers') {
        for(let i = 0; i < geoLayers.length; i++) {
          await fetch("https://brms.ddev.site/geolayer/geojson/" + geoLayers[i],{
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
            },
          }).then(function(response) {
             response.json().then(function(data) {
              data.features.forEach(function(feature) {
                const layer = instance.addLayer('geojson', {
                  title: 'Plan asset',
                  geojson: feature,
                })
                if(geoLayers.length == 1) {
                  instance.zoomToLayer(layer);
                }
               });
             });
          });
        }
        instance.zoomToVectors();
        if(geoLayers.length > 1) {
          instance.map.getView().setZoom((instance.map.getView().getZoom() - 1.5))
        }
      } else {
        // adding single layer
        if (drupalSettings.geolayer_map[instance.target].wkt) {
          var wkt = drupalSettings.geolayer_map[instance.target].wkt;
          var type = 'vector';
          var opts = {
            title: 'Geometry',
            color: 'orange',
          };
          if (wkt !== '' && wkt !== 'GEOMETRYCOLLECTION EMPTY') {
            type = 'wkt';
            opts.wkt = wkt;
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
          // @todo this causes js error relating to getChangeEventType on node forms.
          //instance.addBehavior('snappingGrid');
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
      }
    },
    weight: 100,
  };
}());
