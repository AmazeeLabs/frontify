<?php

namespace Drupal\frontify\Plugin\media\Source;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\frontify\Form\FrontifyMediaVideoForm;
use Drupal\media\Attribute\MediaSource;
use Drupal\media\MediaTypeInterface;

/**
 * Provides a media source plugin for Frontify remote videos.
 */
#[MediaSource(
  id: "frontify_video",
  label: new TranslatableMarkup("Frontify Video"),
  description: new TranslatableMarkup("Frontify remote media video."),
  allowed_field_types: ["frontify_asset_field"],
  default_thumbnail_filename: "no-thumbnail.png",
  thumbnail_alt_metadata_attribute: "thumbnail_alt_value",
  forms: [
    "media_library_add" => FrontifyMediaVideoForm::class,
  ],
)]
class FrontifyVideo extends MediaFrontifySourceBase {

  /**
   * {@inheritdoc}
   */
  public function createSourceField(MediaTypeInterface $type): FieldConfigInterface|EntityInterface {
    return parent::createSourceField($type)->set('label', 'Frontify Video');
  }

}
