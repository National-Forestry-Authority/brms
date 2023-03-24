(function (Drupal) {
  Drupal.behaviors.geolayer_map = {
    attach: function (context, settings) {
      // Get the units.
      let units = 'metric';
      if (!!drupalSettings.geolayer_map.units) {
        units = drupalSettings.geolayer_map.units;
      }

      // Build default options.
      const defaultOptions = {
        units,
        interactions: {
          onFocusOnly: true
        },
      };
      context.querySelectorAll('.geolayer-map').forEach(function (element) {

        // Only create a map once per element.
        if (element.getAttribute('processed')) return;
        element.setAttribute('processed', true);

        element.setAttribute('tabIndex', 0);
        const mapId = element.getAttribute('id');
        const mapOptions = { ...defaultOptions, ...drupalSettings.geolayer_map[mapId].instance};
        const instance = geolayer.map.create(mapId, mapOptions);
        context.querySelectorAll('.ol-popup-closer').forEach(function (element) {
          element.onClick = function (element) {
            element.focus();
          };
        });

        // If the map is rendered as part of a form field, update the map size
        // when the field's visible state changes,
        const formWrapper = element.closest('div.form-wrapper');
        if (formWrapper != null) {

          // When on the modal edit form the map dimensions are not set soon
          // enough so we need to refresh by forcing updateSize.
          const modal = element.closest('div#drupal-modal');
          if (modal != null) {
            setTimeout(function () {
              // Update the map size of the map widget.
              geolayer.map.instances.forEach(function (instance) {
                if (instance.target.startsWith('geolayer-map-geofield-widget')) {
                  instance.map.updateSize();
                  instance.map.getView().setZoom(14);
                }
              });
            }, 200);
          }

          const formWrapperObserver = new MutationObserver((mutations) => {

            // Only update the map size if the wrapper was previously hidden.
            if (mutations.some((mutation) => { return mutation.oldValue.includes('display: none')})) {
              instance.map.updateSize();
            }
          });

          // Observe the style attribute.
          formWrapperObserver.observe(formWrapper, {
            attributeFilter: ["style"],
            attributeOldValue: true
          })
        }

        // If the map is inside a details element, update the map size when
        // the details element is toggled.
        const details = element.closest('details');
        if (details != null) {
          details.addEventListener('toggle', function () {
            instance.map.updateSize();
          });
        }
      });

      // Add an event listener to update the map size when the Gin toolbar is toggled.
      if (context === document) {
        document.addEventListener('toolbar-toggle', function (e) {

          // Only continue if map instances are provided.
          if (typeof geolayer !== 'undefined' && geolayer.map.instances !== 'undefined') {

            // Set a timeout so the computed CSS properties are applied
            // before updating the map size.
            setTimeout(function () {
              // Update the map size of all map instances.
              geolayer.map.instances.forEach(function (instance) {
                instance.map.updateSize();
              });

            }, 200);
          }
        });
      }
    }
  };
}(Drupal));
