<?php

namespace Drupal\frontify;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;

final class FrontifyUtils {

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected $imageStyles;


  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->imageStyles = $entity_type_manager->getStorage('image_style');
  }

  /**
   * Sends a request to the image styles to generate derivatives for the given URL.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function warmImageStyles($url): void {
    if (!function_exists('imagecache_external_generate_path')) {
      \Drupal::logger('frontify')->warning('Function imagecache_external_generate_path is not available. Make sure the imagecache_external module is installed and enabled.');
      return;
    }

    $local_uri = imagecache_external_generate_path($url);

    $available_styles = $this->imageStyles->loadMultiple();
    foreach ($available_styles as $style) {
      $derivative_uri = $style->buildUri($local_uri);
      if (!file_exists($derivative_uri)) {
        $style->createDerivative($local_uri, $derivative_uri);
      }
    }
  }
}
