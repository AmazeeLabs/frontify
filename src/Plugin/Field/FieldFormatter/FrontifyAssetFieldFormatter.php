<?php

namespace Drupal\frontify\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\image\ImageStyleStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Frontify Asset field formatter.
 *
 * @todo make it specific to Frontify Image, images doesn't apply to other media types.
 */
#[FieldFormatter(
  id: "frontify_asset_field_formatter",
  label: new TranslatableMarkup("Frontify asset image style"),
  description: new TranslatableMarkup("Displays the Frontify asset with image style."),
  field_types: ["frontify_asset_field"],
)]
class FrontifyAssetFieldFormatter extends FormatterBase {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected AccountInterface $currentUser;

  /**
   * The image style entity storage.
   *
   * @var \Drupal\image\ImageStyleStorageInterface
   */
  protected ImageStyleStorageInterface $imageStyleStorage;

  /**
   * Constructs an ImageFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityStorageInterface $image_style_storage
   *   The image style storage.
   */
  public function __construct(
    string $plugin_id,
    mixed $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    string $label,
    string $view_mode,
    array $third_party_settings,
    AccountInterface $current_user,
    ImageStyleStorageInterface $image_style_storage
  ) {
    parent::__construct(
      $plugin_id,
      $plugin_definition,
      $field_definition,
      $settings,
      $label,
      $view_mode,
      $third_party_settings);
    $this->currentUser = $current_user;
    $this->imageStyleStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ): FormatterBase|FrontifyAssetFieldFormatter|ContainerFactoryPluginInterface|static {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('current_user'),
      $container->get('entity_type.manager')?->getStorage('image_style')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    return [
      'image_style' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $image_styles = image_style_options(FALSE);
    $description_link = Link::fromTextAndUrl(
      $this->t('Configure Image Styles'),
      Url::fromRoute('entity.image_style.collection')
    );
    $element['image_style'] = [
      '#title' => $this->t('Image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('image_style'),
      '#empty_option' => $this->t('None (original image)'),
      '#options' => $image_styles,
      '#description' => $description_link->toRenderable() + [
        '#access' => $this->currentUser->hasPermission('administer image styles'),
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = [];

    $image_styles = image_style_options(FALSE);
    // Unset possible 'No defined styles' option.
    unset($image_styles['']);
    $image_style_setting = $this->getSetting('image_style');
    if (isset($image_styles[$image_style_setting])) {
      $summary[] = $this->t('Image style: @style', ['@style' => $image_styles[$image_style_setting]]);
    }
    else {
      $summary[] = $this->t('Original image');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $element = [];
    $base_cache_tags = [];
    $base_cache_context = [];
    $width = NULL;
    $entity = $items->getEntity();
    $image_style_setting = $this->getSetting('image_style');
    $base_cache_context[] = 'url.site';
    if (!empty($image_style_setting)) {
      $image_style = $this->imageStyleStorage->load($image_style_setting);
      if ($image_style) {
        $effect = $image_style->getEffects()->getConfiguration();
        $effect = reset($effect);
        if ($effect['data']['width']) {
          $width = $effect['data']['width'];
        }
        $base_cache_tags = $image_style->getCacheTags();
        $base_cache_context = $image_style->getCacheContexts();
      }
    }
    // @todo handle multiple Frontify media types.
    foreach ($items as $delta => $item) {
      $values = $item->getValue();
      $element[$delta] = [
        '#theme' => 'frontify_image_formatter',
        '#uri' => strtr($values['uri'], ['{width}' => $width]),
        '#id' => $values['id'],
        '#alt' => $values['name'],
        '#width' => $width,
        '#cache' => [
          'tags' => Cache::mergeTags($base_cache_tags, $entity->getCacheTags()),
          'contexts' => Cache::mergeContexts($base_cache_context, $entity->getCacheContexts()),
        ],
      ];
    }

    return $element;
  }

}
