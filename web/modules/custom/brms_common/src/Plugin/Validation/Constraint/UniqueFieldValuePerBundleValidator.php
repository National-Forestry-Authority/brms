<?php

namespace Drupal\brms_common\Plugin\Validation\Constraint;

use Drupal\Core\Validation\Plugin\Validation\Constraint\UniqueFieldValueValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validates that a field is unique for the given entity type and bundle.
 */
class UniqueFieldValuePerBundleValidator extends UniqueFieldValueValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($items, Constraint $constraint) {
    if (!$items->first()) {
      return;
    }

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $items->getEntity();
    $entity_type = $entity->getEntityType();
    $entity_type_id = $entity_type->id();
    $entity_label = $entity->getEntityType()->getSingularLabel();

    $field_name = $items->getFieldDefinition()->getName();
    $field_label = $items->getFieldDefinition()->getLabel();
    $field_storage_definitions = $this->entityFieldManager->getFieldStorageDefinitions($entity_type_id);
    $property_name = $field_storage_definitions[$field_name]->getMainPropertyName();

    $id_key = $entity_type->getKey('id');
    $is_multiple = $field_storage_definitions[$field_name]->isMultiple();
    $is_new = $entity->isNew();
    $item_values = array_column($items->getValue(), $property_name);

    // Check if any item values for this field already exist in other entities
    // of the same bundle.
    $query = $this->entityTypeManager
      ->getStorage($entity_type_id)
      ->getAggregateQuery()
      ->accessCheck(FALSE)
      ->condition($field_name, $item_values, 'IN')
      ->condition('type', $entity->bundle())
      ->groupBy("$field_name.$property_name");
    if (!$is_new) {
      $entity_id = $entity->id();
      $query->condition($id_key, $entity_id, '<>');
    }
    $results = $query->execute();

    if (!empty($results)) {
      $column_key = key(reset($results));
      $other_entity_values = array_column($results, $column_key);
      $duplicate_values = $this->caseInsensitiveArrayIntersect($item_values, $other_entity_values);

      foreach ($duplicate_values as $delta => $dupe) {
        $violation = $this->context
          ->buildViolation($constraint->message)
          ->setParameter('@entity_type', $entity_label)
          ->setParameter('@field_name', $field_label)
          ->setParameter('%value', $dupe);
        if ($is_multiple) {
          $violation->atPath((string) $delta);
        }
        $violation->addViolation();
      }
    }

    // Check if items are duplicated within this entity.
    if ($is_multiple) {
      $duplicate_values = $this->extractDuplicates($item_values);
      foreach ($duplicate_values as $delta => $dupe) {
        $this->context
          ->buildViolation($constraint->message)
          ->setParameter('@entity_type', $entity_label)
          ->setParameter('@field_name', $field_label)
          ->setParameter('%value', $dupe)
          ->atPath((string) $delta)
          ->addViolation();
      }
    }
  }

  /**
   * Do a case-insensitive array intersection, and keep original capitalization.
   *
   * @param array $orig_values
   *   The original values to be returned.
   * @param array $comp_values
   *   The values to intersect $orig_values with.
   *
   * @return array
   *   Elements of $orig_values contained in $comp_values when ignoring
   *   capitalization.
   */
  private function caseInsensitiveArrayIntersect(array $orig_values, array $comp_values): array {
    $lowercase_comp_values = array_map('strtolower', $comp_values);
    $intersect_map = array_map(fn (string $x) => in_array(strtolower($x), $lowercase_comp_values, TRUE) ? $x : NULL, $orig_values);

    return array_filter($intersect_map, function ($x) {
      return $x !== NULL;
    });
  }

}
