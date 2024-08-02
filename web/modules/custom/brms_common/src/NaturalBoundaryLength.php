<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Computes the value of the natural boundary length field.
 *
 * @package Drupal\brms_common
 */
class NaturalBoundaryLength extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getParent()->getValue();

    if ($node->hasField('riverline_length_computed') && $node->hasField('shoreline_length_computed') && $node->hasField('interprotected_area_length_computed')) {
      $calculated_value = ($node->riverline_length_computed->getString() ?: 0)
        + ($node->shoreline_length_computed->getString() ?: 0)
        + ($node->interprotected_area_length_computed->getString() ?: 0);

      if ($calculated_value > 0) {
        $this->list[0] = $this->createItem(0, $calculated_value);
      }
    }
  }

}
