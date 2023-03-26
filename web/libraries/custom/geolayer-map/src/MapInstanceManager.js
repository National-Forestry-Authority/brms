// Import geolayer map instance factory function.
import createInstance from './instance/instance';

// Import geolayer-map CSS.
import './styles.css';

import namedBehaviors from './behavior';


class MapInstanceManager {

  constructor() {

    // Initialize an array of geolayer map instances.
    this.instances = [];

    // Behaviors which can be added on-demand by name via `instance.addBehavior`
    // These are objects with an attach() method that will be called when a map is
    // created. They receive the instance object so that they can perform
    // modifications to the map using instance methods.
    this.namedBehaviors = { ...namedBehaviors };

    // Map behaviors which will be automatically attached to all created map
    // instances.
    this.behaviors = {
      loading: namedBehaviors.loading,
      rememberLayer: namedBehaviors.rememberLayer,
    };

  }

  // Create a new geolayer map attached to a target element ID and add it to the
  // global instances array.
  create(target, options) {
    const instance = createInstance({ target, options });

    instance.instanceManager = this;

    // Add the instance to the array.
    this.instances.push(instance);

    // Attach behaviors from geolayer.map.behaviors to the instance in order of weight - if any.
    instance.defaultBehaviorsAttached = instance.attachBehaviorsByWeight(
      Object.values(this.behaviors),
    );

    // Return the instance.
    return instance;
  }

  // Destroy a geolayer map instance and remove it from the instances array.
  destroy(target) {
    const i = this.targetIndex(target);
    if (i > -1) {
      this.instances[i].map.setTarget(null);
      this.instances.splice(i, 1);
    }
  }

  // Look up an instance index based on its target element ID.
  targetIndex(target) {
    return this.instances.findIndex(instance => instance.target === target);
  }
}

export default MapInstanceManager;
