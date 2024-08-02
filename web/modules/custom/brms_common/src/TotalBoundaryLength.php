<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Computes the value of the total boundary length field.
 *
 * @package Drupal\brms_common
 */
class TotalBoundaryLength extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getParent()->getValue();

    if ($node->hasField('cutline_length_computed') && $node->hasField('natural_boundary_length_computed')) {
      $calculated_value = ($node->cutline_length_computed->getString() ?: 0)
        + ($node->natural_boundary_length_computed->getString() ?: 0);

      if ($calculated_value > 0) {
        $this->list[0] = $this->createItem(0, $calculated_value);
      }
    }
  }

}
