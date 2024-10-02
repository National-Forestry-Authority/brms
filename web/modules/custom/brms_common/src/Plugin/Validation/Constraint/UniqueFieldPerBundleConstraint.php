<?php

namespace Drupal\brms_common\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Checks if an entity field has a unique value in entities of the same bundle.
 */
#[Constraint(
  id: 'UniqueFieldPerBundle',
  label: new TranslatableMarkup('Unique field per bundle constraint', [], ['context' => 'Validation'])
)]
class UniqueFieldPerBundleConstraint extends SymfonyConstraint {

  /**
   * The message that will be shown if the field is not unique.
   *
   * @var string
   */
  public $message = 'A record with @field_name %value already exists.';

  /**
   * Returns the name of the class that validates this constraint.
   *
   * @return string
   *   The class name of the validator.
   */
  public function validatedBy() {
    return '\Drupal\brms_common\Plugin\Validation\Constraint\UniqueFieldValuePerBundleValidator';
  }

}
