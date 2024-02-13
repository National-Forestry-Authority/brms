<?php

namespace Drupal\brms_common;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComputedItemListTrait;

class NaturalBoundaryLength extends FieldItemList implements FieldItemListInterface {

  use ComputedItemListTrait;

  /**
   * Compute the values.
   */
  protected function computeValue() {
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getParent()->getValue();
    error_log(print_r('testing test', TRUE));

    if ($node->hasField('riverline_length') && $node->hasField('shoreline_length') && $node->hasField('protected_area_length')) {
      $calculated_value = ($node->riverline_length->getString() ?: 0)
        + ($node->shoreline_length->getString() ?: 0)
        + ($node->protected_area_length->getString() ?: 0);
      error_log(print_r($calculated_value, TRUE));
      $this->list[0] = $this->createItem(0, $calculated_value);
    }
  }

}
