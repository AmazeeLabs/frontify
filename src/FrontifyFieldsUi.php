<?php

declare(strict_types=1);

namespace Drupal\frontify;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Psr\Http\Client\ClientInterface;

/**
 * Builds the Frontify UI fields.
 * You can change one thing here and have it reflected across the entire UI.
 */
final class FrontifyFieldsUi {

  use StringTranslationTrait;

  /**
   *
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
  ) {}

  public function mediaLibraryUi(
    string $frontify_wrapper_class = NULL,
    string $trigger_element = NULL,
    string $trigger_event = NULL,
    string $select_add_button_class = NULL,
    bool $hide_open_button = TRUE,
    bool $enable_image_preview = FALSE,
    string $frontify_conext = 'media_library'): array {
    $fields = [];

    $config = \Drupal::config('frontify.settings');
    $frontifyApiUrl = $config->get('frontify_api_url');
    $frontifyDebugMode = $config->get('debug_mode');

    if (empty($frontifyApiUrl)) {
      $fields['message'] = [
        '#theme' => 'status_messages',
        '#message_list' => [
          'error' => [$this->t('Frontify API URL is not set. Please configure it in the Frontify settings.')],
        ],
      ];
      return $fields;
    }

    // Add a container to group the input elements for styling purposes.
    $fields['container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['frontify-media-library-wrapper'],
      ],
    ];

    $fields['container']['open'] = [
      '#type' => 'button',
      '#value' => $this->t('Open Frontify'),
      '#attributes' => [
        'class' => ['frontify-finder-open-button', 'btn', 'btn-primary'],
        'id' => ['frontify-finder-open-button'],
      ],
      '#attached' => [
        'library' => [
          'frontify/frontify_once',
          'frontify/frontify_media_library',
        ],
        'drupalSettings' => [
          'Frontify' =>
            [
              'api_url' => $frontifyApiUrl,
              'debug_mode' => $frontifyDebugMode === 1,
              'hide_open_button' => $hide_open_button,
              'enable_image_preview' => $enable_image_preview,
            ],
        ],
      ],
      '#limit_validation_errors' => [],
    ];

    if ($frontify_wrapper_class) {
      $fields['container']['open']['#attached']['drupalSettings']['Frontify']['wrapper_class'] = $frontify_wrapper_class;
    }

    if ($frontify_conext) {
      $fields['container']['open']['#attached']['drupalSettings']['Frontify']['context'] = $frontify_conext;
    }

    if ($trigger_element) {
      $fields['container']['open']['#attached']['drupalSettings']['Frontify']['trigger_element'] = $trigger_element;
    }

    if ($trigger_event) {
      $fields['container']['open']['#attached']['drupalSettings']['Frontify']['trigger_event'] = $trigger_event;
    }

    if ($select_add_button_class) {
      $fields['container']['open']['#attached']['drupalSettings']['Frontify']['select_add_button_class'] = $select_add_button_class;
    }

    $fields['container']['frontify_finder_wrapper'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['frontify-finder-wrapper'],
      ],
    ];

    $fields['container']['name_wrapper']['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Frontify image name'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['frontify-name'],
      ],
    ];
    $fields['container']['image_preview_wrapper']['image_preview'] = [
      '#type' => 'markup',
      '#markup' => '<div class="frontify-image-preview"></div>',
    ];
    $fields['container']['frontify_fields'] = [
      '#title' => $this->t('Frontify fields'),
      '#type' => 'details',
      '#open' => FALSE,
      '#attributes' => [
        'class' => [
          'frontify-fields',
          $frontifyDebugMode !== 1 ? 'visually-hidden' : '',
        ],
      ],
    ];
    $fields['container']['frontify_fields']['id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Frontify image id'),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['frontify-id'],
      ],
    ];
    $fields['container']['frontify_fields']['uri'] = [
      '#type' => 'url',
      '#title' => $this->t('Frontify image URL'),
      '#maxlength' => 2048,
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['frontify-image-uri'],
      ],
    ];
    $fields['container']['frontify_fields']['metadata'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Frontify image metadata'),
      '#required' => TRUE,
      '#attributes' => [
        'class' => ['frontify-image-metadata'],
      ],
    ];

    return $fields;
  }

}
