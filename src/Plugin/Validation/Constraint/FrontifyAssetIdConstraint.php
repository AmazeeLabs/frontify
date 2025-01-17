<?php

declare(strict_types=1);

namespace Drupal\frontify\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Provides a Frontify Asset ID constraint.
 *
 * @Constraint(
 *   id = "FrontifyAssetId",
 *   label = @Translation("Frontify Asset ID", context = "Validation"),
 * )
 *
 * @DCG
 * To apply this constraint on third party field types. Implement
 * hook_field_info_alter() as follows.
 * @code
 * function frontify_field_info_alter(array &$info): void {
 *   $info['FIELD_TYPE']['constraints']['FrontifyAssetId'] = [];
 * }
 * @endcode
 *
 * @see https://www.drupal.org/node/2015723
 */
final class FrontifyAssetIdConstraint extends Constraint {

  public string $message = 'The Frontify id <em>@id</em> is already used by <a href=":duplicate_media_url" target="_blank">this Media entity</a>, it should be unique, or you need to allow duplicates in the media type configuration.';

}
