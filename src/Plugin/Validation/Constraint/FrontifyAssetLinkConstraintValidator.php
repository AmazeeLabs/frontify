<?php

namespace Drupal\frontify\Plugin\Validation\Constraint;

use InvalidArgumentException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for links receiving data allowed by its settings.
 */
class FrontifyAssetLinkConstraintValidator extends ConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint): void {
    if (isset($value)) {
      $uri_is_valid = TRUE;
      $link_item = $value;
      // Try to resolve the given URI to a URL. It may fail if it's schemeless.
      try {
        $url = $link_item->getUrl();
      }
      catch (InvalidArgumentException) {
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
