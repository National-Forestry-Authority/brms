<?php

namespace Drupal\geolayer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a geolayer entity type.
 */
interface GeolayerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
