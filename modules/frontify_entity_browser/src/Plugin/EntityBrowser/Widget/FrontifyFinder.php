<?php

namespace Drupal\frontify_entity_browser\Plugin\EntityBrowser\Widget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_browser\WidgetBase;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Exposes the Frontify Finder.
 *
 * @EntityBrowserWidget(
 *   id = "frontify_finder",
 *   label = @Translation("Frontify Finder"),
 *   description = @Translation("Expose the Frontify Finder."),
 *   auto_select = TRUE
 * )
 */
class FrontifyFinder extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array_merge(parent::defaultConfiguration(), [
      'submit_text' => $this->t('Select'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$original_form, FormStateInterface $form_state, array $additional_widget_parameters) {
    $form = parent::getForm($original_form, $form_state, $additional_widget_parameters);

    /** @var \Drupal\frontify\FrontifyFieldsUi $fontifyFieldUiService */
    $fontifyFieldUiService = \Drupal::service('frontify.fields.ui');

    $fields = $fontifyFieldUiService->mediaLibraryUi('.entity-browser-form');

    if (isset($fields['message'])) {
      return $fields;
    }

    $form = array_merge($form, $fields);

//    $config = \Drupal::config('frontify.settings');
//
//    $form['frontify_wrapper_overlay'] = [
//      '#type' => 'container',
//      '#attributes' => [
//        'class' => ['frontify-wrapper-finder-overlay'],
//      ],
//    ];
//
//    $form['frontify_wrapper_overlay']['frontify_wrapper'] = [
//      '#type' => 'container',
//      '#attributes' => [
//        'class' => ['frontify-finder-wrapper'],
//      ],
//    ];
//
//    $form['open'] = [
//      '#type' => 'button',
//      '#value' => $this->t('Open Frontify'),
//      '#attributes' => [
//        'class' => ['frontify-finder-open-button', 'btn', 'btn-primary'],
//        'id' => ['frontify-asset-insert-button'],
//      ],
//      '#attached' => [
//        'library' => [
//          'frontify/frontify_once',
//          'frontify/frontify_media_library',
//        ],
//        'drupalSettings' => [
//          'Frontify' => [
//            'context' => 'media_library',
//            'wrapper_class' => '.entity-browser-form',
//            'api_url' => $config->get('frontify_api_url'),
//            'debug_mode' => $config->get('debug_mode'),
//          ],
//        ],
//      ],
//      '#limit_validation_errors' => [],
//    ];
//
//    $form['uri'] = [
//      '#type' => 'url',
//      '#default_value' => '',
//      '#element_validate' => [[static::class, 'validateUriElement']],
//      '#maxlength' => 2048,
//      '#attributes' => [
//        'class' => ['frontify-asset-link-url', 'hidden'],
//      ],
//    ];
//    $form['frontify_fields'] = [
//      '#type' => 'details',
//      '#title' => $this->t('Frontify fields'),
//      '#open' => FALSE,
//      '#attributes' => [
//        'class' => ['frontify-asset-details'],
//      ],
//    ];
//    $form['frontify_fields']['name'] = [
//      '#type' => 'textfield',
//      '#title' => $this->t('Name'),
//      '#default_value' => '',
//      '#maxlength' => 255,
//      '#attributes' => [
//        'class' => ['frontify-asset-name'],
//      ],
//    ];
//    $form['frontify_fields']['id'] = [
//      '#type' => 'textfield',
//      '#title' => $this->t('ID'),
//      '#default_value' => '',
//      '#maxlength' => 255,
//      '#attributes' => [
//        'class' => ['frontify-asset-id'],
//      ],
//    ];
//    // @todo we could optionally use a JSON field.
//    $form['frontify_fields']['metadata'] = [
//      '#type' => 'textarea',
//      '#title' => $this->t('Metadata'),
//      '#default_value' => '',
//      '#description' => $this->t(
//        'Snapshot of the metadata used during the import.'
//      ),
//      '#attributes' => [
//        'class' => ['frontify-asset-metadata'],
//      ],
//    ];

    return $form;
  }

  protected function prepareEntities(array $form, FormStateInterface $form_state) {
    // TODO: Implement prepareEntities() method.
    return [];
  }

}
