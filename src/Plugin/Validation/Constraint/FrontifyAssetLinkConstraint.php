<?php

declare(strict_types=1);

namespace Drupal\frontify\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for Frontify asset links.
 *
 * @Constraint(
 *   id = "FrontifyAssetLink",
 *   label = @Translation("Link data valid for Frontify asset.", context="Validation"),
 * )
 */
final class FrontifyAssetLinkConstraint extends Constraint {

  /**
   * The message.
   *
   * @var string
   */
  public string $message = "The path ':uri' is invalid.";

}
