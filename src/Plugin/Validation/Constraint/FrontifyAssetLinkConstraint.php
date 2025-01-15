<?php

namespace Drupal\frontify\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for frontify asset links.
 *
 * @Constraint(
 *   id = "FrontifyAssetLink",
 *   label = @Translation("Link data valid for frontifyAsset.", context="Validation"),
 * )
 */
class FrontifyAssetLinkConstraint extends Constraint {

  /**
   * The message.
   *
   * @var string
   */
  public string $message = "The path ':uri' is invalid.";

}
