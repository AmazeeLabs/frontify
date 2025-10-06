<?php

namespace Drupal\frontify;

use Drupal\media_library\MediaLibraryState;
use Drupal\media_library\MediaLibraryUiBuilder as BaseMediaLibraryUiBuilder;

/**
 * Service which builds the media library.
 *
 * Remove the MediaLibraryView for Frontify media type.
 */
class MediaLibraryUiBuilder extends BaseMediaLibraryUiBuilder {

  /**
   * {@inheritdoc}
   */
  protected function buildLibraryContent(MediaLibraryState $state) {
    $opener = $state->getOpenerId();
    // @todo handle all possible media types created by the Frontify source plugin.
    $isFrontifyMediaType = in_array($state->getSelectedTypeId(), ['frontify_image', 'frontify_video']);
    // The Gutenberg implementation requires to get the media library view
    // in the DOM.
    $hideMediaLibraryView = $isFrontifyMediaType && $opener !== 'gutenberg.media_library.opener';
    return [
      '#type' => 'container',
      '#theme_wrappers' => [
        'container__media_library_content',
      ],
      '#attributes' => [
        'id' => 'media-library-content',
      ],
      'form' => $this->buildMediaTypeAddForm($state),
      'view' => $hideMediaLibraryView ? NULL : $this->buildMediaLibraryView($state),
    ];
  }

}
