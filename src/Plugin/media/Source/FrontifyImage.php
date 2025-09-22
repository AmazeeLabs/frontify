<?php

namespace Drupal\frontify\Plugin\media\Source;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\frontify\Form\FrontifyMediaImageForm;
use Drupal\media\Attribute\MediaSource;
use Drupal\media\MediaTypeInterface;

/**
 * Provides a media source plugin for Frontify remote images.
 */
#[MediaSource(
  id: "frontify_image",
  label: new TranslatableMarkup("Frontify Image"),
  description: new TranslatableMarkup("Frontify remote media image."),
  allowed_field_types: ["frontify_asset_field"],
  default_thumbnail_filename: "no-thumbnail.png",
  thumbnail_alt_metadata_attribute: "thumbnail_alt_value",
  forms: [
    "media_library_add" => FrontifyMediaImageForm::class,
  ],
)]
class FrontifyImage extends MediaFrontifySourceBase {

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type): FieldConfigInterface|EntityInterface {
    return parent::createSourceField($type)->set('label', 'Frontify Image');
  }

}
