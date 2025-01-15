<?php declare(strict_types = 1);

namespace Drupal\frontify;

use Drupal\graphql_directives\DirectiveArguments;
use Drupal\Component\Serialization\Json;

/**
 * Frontify directives.
 *
 * Integration with GraphQL v4 directives to produce responsive images.
 *
 * Port of Drupal\silverback_cloudinary\Plugin\GraphQL\DataProducer\ResponsiveImage.
 */
final class FrontifyDirective {

  const QUALITY = 90;

  /**
   * Returns Frontify image props.
   *
   * @param \Drupal\graphql_directives\DirectiveArguments $args
   *
   * @return array|null
   */
  public function imageProps(DirectiveArguments $args): ?array {
    if (empty($args->value)) {
      return NULL;
    }

    $imageSize = getimagesize($args->value);

    return [
      'src' => $args->value,
      'width' => $imageSize[0],
      'height' => $imageSize[1],
    ];
  }

  /**
   * Returns Frontify responsive image props.
   *
   * @param \Drupal\graphql_directives\DirectiveArguments $args
   *
   * @return string|null
   */
  public function frontifyResponsiveImage(DirectiveArguments $args): ?string {
    if (empty($args->value)) {
      return NULL;
    }

    $image = $args->value;
    $width = $image['width'];
    $height = $image['height'];
    $sizes = $args->args['sizes'] ?? [];

    $return = $image;
    $return['originalSrc'] = $image['src'];

    // If no width is given, we just return the original image url.
    if (empty($width) || empty($args->args['sizes'])) {
      return Json::encode($return);
    }
    $ratio = $image['height'] / $image['width'];
    // The image width and height in the response should be the same as the ones
    // sent as parameters.
    // @todo: Unless the width sent is bigger than the width of the original
    // image, since we should not scale up. TBD what to do in this case.
    $return['width'] = $width;
    $return['height'] = $height ?: round($width * $ratio);
    if (!empty($sizes)) {
      $return['sizes'] = $this->buildSizesString($sizes, $width);
      $return['srcset'] = $this->buildSrcSetString($image['src'], $sizes, ['width' => $width, 'height' => $height]);
    }
    $return['src'] = $this->getFrontifyImageUrl($image['src'], ['width' => $width, 'height' => $height]);

    $return = array_filter($return);

    return $return ? Json::encode($return) : NULL;
  }

  /**
   * Builds a sizes string from a sizes array.
   *
   * @param array $sizes
   *  An array of image sizes.
   *  Example: [
   *    [400, 390] -> up until 400px screen width, use the 390px image
   *    [800, 780] -> up until 800px screen width, use the 780px image
   *  ]
   * @param int $default_width
   *  The default width to add at the end of the $sizes string.
   *
   * @return string
   */
  protected function buildSizesString(array $sizes, $default_width = NULL): string {
    if (empty($sizes)) {
      return '';
    }
    $sizeEntries = array_reduce($sizes, function ($carry, $sizesElement) {
      // Each size must have exactly 2 elements.
      if (count($sizesElement) !== 2) {
        return $carry;
      }
      $carry[] = "(max-width: $sizesElement[0]px) $sizesElement[1]px";
      return $carry;
    }, []);

    // At the end, add the default width.
    if (!empty($default_width)) {
      $sizeEntries[] = $default_width . 'px';
    }
    return !empty($sizeEntries) ? implode(', ', $sizeEntries) : '';
  }

  /**
   * Builds a srcset string for an original image, based on a sizes array.
   *
   * @param string $originalUrl
   *  The original image url
   * @param array $sizes
   *  A sizes array, same is in buildSizesString().
   * @param array $defaultDimensions
   *  The default dimensions (width and, optionally, height) of the image so
   *  that we can compute the height of each of the image in the src set, by
   *  preserving the aspect ratio.
   *
   * @return string
   */
  protected function buildSrcSetString($originalUrl, array $sizes, array $defaultDimensions = []): string {
    if (empty($sizes)) {
      return '';
    }
    $srcSetEntries = array_reduce($sizes, function ($carry, $sizesElement) use ($defaultDimensions, $originalUrl) {
      // Each size must have exactly 2 elements.
      if (count($sizesElement) !== 2) {
        return $carry;
      }
      $imageConfig = [
        'width' => $sizesElement[1],
      ];
      // If we know the default dimensions of the image, and the width of the
      // desired one, we can also calculate the height of it.
      if (!empty($defaultDimensions['width']) && !empty($defaultDimensions['height'])) {
        $imageConfig['height'] = (int) round(($imageConfig['width'] * $defaultDimensions['height']) / $defaultDimensions['width']);
      }
      $carry[] = $this->getFrontifyImageUrl($originalUrl, $imageConfig) . ' ' . $imageConfig['width'] . 'w';
      return $carry;
    }, []);

    if (empty($srcSetEntries)) {
      return '';
    }
    return implode(', ', $srcSetEntries);
  }

  /**
   * Helper method to return a simple Frontify image url for an image url and
   * a config array.
   *
   * The config array can contain a width and a height.
   *
   * @param string $originalUrl
   * @param array $config
   *
   * @return string
   */
  protected function getFrontifyImageUrl(string $originalUrl, array $config = []): string {
    $result = $originalUrl . '?quality=' . self::QUALITY;
    if (empty($config['width']) && empty($config['height'])) {
      return $result;
    }

    if (!empty($config['width'])) {
      $result .= '&width=' . $config['width'];
    }
    if (!empty($config['height'])) {
      $result .= '&height=' . $config['height'];
    }

    return $result;
  }

}
