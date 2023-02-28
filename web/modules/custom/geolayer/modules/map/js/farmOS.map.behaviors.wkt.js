(function () {
  farmOS.map.behaviors.wkt = {
    attach: async function (instance) {
      // If WKT was set, create a layer.
      console.log(drupalSettings.farm_map[instance.target].entity_type);
      const geoLayers = drupalSettings.farm_map[instance.target].geolayers;
      const entityType = drupalSettings.farm_map[instance.target].entity_type;
      console.log(geoLayers);
      if(entityType !== 'Geolayer') {
        for(let i = 0; i < geoLayers.length; i++) {
          await fetch("https://brms.ddev.site/geolayer/geojson/" + geoLayers[i],{
            method: 'GET',
            headers: {
              'Content-Type': 'application/json',
            },
          }).then(function(response) {
             response.json().then(function(data) {
              data.features.forEach(function(feature) {
                instance.addLayer('geojson', {
                  title: 'Plan asset',
                  geojson: feature,
                })
               });
             });
          });
        }
        instance.zoomToVectors()
        instance.map.getView().setZoom((instance.map.getView().getZoom() - 1.5))
      }else{
        // adding single layer
        if (drupalSettings.farm_map[instance.target].wkt) {
          var wkt = drupalSettings.farm_map[instance.target].wkt;
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
        if (drupalSettings.farm_map[instance.target].behaviors.wkt.edit) {
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
