// Import geolayer map instance factory function.
import createInstance from './instance/instance';

// Import geolayer-map CSS.
import './styles.css';

// Define window.geolayer if it isn't already.
if (typeof window.geolayer === 'undefined') {
  window.geolayer = {};
}

// Add a geolayer.map object that is available globaly.
window.geolayer.map = {

  // Initialize an array of geolayer map instances.
  instances: [],

  // Initialize map behaviors.
  // These are objects with an attach() method that will be called when a map is
  // created. They receive the instance object so that they can perform
  // modifications to the map using instance methods.
  behaviors: {},

  // Create a new geolayer map attached to a target element ID and add it to the
  // global instances array.
  create(target, options) {
    const instance = createInstance({ target, options });

    // Add the instance to the array.
    this.instances.push(instance);

    // Attach behaviors from geolayer.map.behaviors to the instance.
    // Sort by an optional weight value on each behavior.
    const behaviors = Object.keys(this.behaviors).map(i => this.behaviors[i]);
    behaviors.sort((first, second) => {
      const firstWeight = first.weight || 0;
      const secondWeight = second.weight || 0;
      if (firstWeight === secondWeight) {
        return 0;
      }
      return (firstWeight < secondWeight) ? -1 : 1;
    });
    behaviors.forEach((behavior) => {
      instance.attachBehavior(behavior);
    });

    // Return the instance.
    return instance;
  },

  // Destroy a geolayer map instance and remove it from the instances array.
  destroy(target) {
    const i = this.targetIndex(target);
    if (i > -1) {
      this.instances[i].map.setTarget(null);
      this.instances.splice(i, 1);
    }
  },

  // Look up an instance index based on its target element ID.
  targetIndex(target) {
    return this.instances.findIndex(instance => instance.target === target);
  },
};
