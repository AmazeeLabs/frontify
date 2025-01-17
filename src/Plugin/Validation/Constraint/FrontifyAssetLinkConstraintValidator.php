<?php

declare(strict_types=1);

namespace Drupal\frontify\Plugin\Validation\Constraint;

use Drupal\frontify\Plugin\Field\FieldType\FrontifyAssetField;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Constraint validator for links receiving data allowed by its settings.
 */
final class FrontifyAssetLinkConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof FrontifyAssetLinkConstraint) {
      throw new UnexpectedTypeException($constraint, FrontifyAssetLinkConstraint::class);
    }

    if (!$value instanceof FrontifyAssetField) {
      throw new \InvalidArgumentException(
        sprintf('The validated value must be instance of \Drupal\frontify\Plugin\Field\FieldType\FrontifyAssetField, %s was given.', get_debug_type($value))
      );
    }

    if (isset($value)) {
      $uri_is_valid = TRUE;
      $link_item = $value;
      // Try to resolve the given URI to a URL. It may fail if it's schemeless.
      try {
        $url = $link_item->getUrl();
      }
      catch (\InvalidArgumentException) {
        $uri_is_valid = FALSE;
      }
      if (!($uri_is_valid && $url->isExternal())) {
        $uri_is_valid = FALSE;
      }

      if (!$uri_is_valid) {
        $this->context->addViolation($constraint->message, [':uri' => $link_item->uri]);
      }
    }
  }

}
