<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

class TotalCutlineLength extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getParent()->getValue();

    if ($node->hasField('total_cutline_length') && $node->hasField('natural_boundary_length')) {
      $calculated_value = $node->total_cutline_length->getString() + $node->natural_boundary_length->getString();
      $this->list[0] = $this->createItem(0, $calculated_value);
    }
  }

}
