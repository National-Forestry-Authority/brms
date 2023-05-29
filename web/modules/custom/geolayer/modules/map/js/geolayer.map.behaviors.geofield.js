(function () {
  nfa.map.behaviors.geofield = {
    attach: function (instance) {
      if (instance.edit) {
        instance.edit.wktOn('featurechange', function(wkt) {
          document.querySelector('#' + instance.target).parentElement.querySelector('textarea').value = wkt;
        });
      }
    },

    // Make sure this runs after nfa.map.behaviors.wkt.
    weight: 101,
  };
}());
