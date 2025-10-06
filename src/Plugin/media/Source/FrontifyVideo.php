<?php

namespace Drupal\frontify\Plugin\media\Source;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\frontify\Form\FrontifyMediaVideoForm;
use Drupal\media\Attribute\MediaSource;
use Drupal\media\MediaInterface;
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

  /**
   * {@inheritdoc}
   */
  public function getMetadata(MediaInterface $media, $attribute_name) {
    $url = $media->get($this->configuration['source_field'])->uri;

    switch ($attribute_name) {
      // Name is already set in FrontifyMediaImageForm::createMediaFromValues().
      case 'thumbnail_width':
        return static::THUMBNAIL_WIDTH;

      case 'thumbnail_height':
        return static::THUMBNAIL_HEIGHT;

      // Thumbnail is by default the video file (mp4, ...), that will trigger
      // FileExtensionContraint limited to 'png gif jpg jpeg webp'.
      // So just use the generic video thumbnail for now.
      // MediaFrontifySourceBase::getLocalThumbnailUri should be extended
      // to generate the actual thumbnail from the video.
      case 'thumbnail_uri':
        return 'public://media-icons/generic/video.png';

      case 'thumbnail_alt_value':
        // Name is fine for this use case.
        return $media->getName();

      default:
        return parent::getMetadata($media, $attribute_name);
    }
  }

}
