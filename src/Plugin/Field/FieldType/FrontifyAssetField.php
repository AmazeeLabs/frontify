<?php

namespace Drupal\frontify\Plugin\Field\FieldType;

use Drupal\Core\Field\Attribute\FieldType;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\Url;

/**
 * Plugin implementation of the 'frontify_asset_field' field type.
 */
#[FieldType(
  id: "frontify_asset_field",
  label: new TranslatableMarkup("Frontify asset"),
  description: new TranslatableMarkup("Stores Frontify uri and metadata."),
  category: "general",
  default_widget: "frontify_asset_field_widget",
  default_formatter: "frontify_asset_field_formatter",
  constraints: ["FrontifyAssetLink" => []],
)]
class FrontifyAssetField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition): array {
    $properties['uri'] = DataDefinition::create('uri')
      ->setLabel(t('URI'));
    $properties['id'] = DataDefinition::create('string')
      ->setLabel(t('ID'));
    $properties['name'] = DataDefinition::create('string')
      ->setLabel(t('Name'));
    $properties['metadata'] = DataDefinition::create('string')
      ->setLabel(t('Metadata (JSON)'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition): array {
    return [
      'columns' => [
        'uri' => [
          'description' => 'The Frontify uri.',
          'type' => 'varchar',
          'length' => 2048,
        ],
        'id' => [
          'description' => 'The Frontify ID.',
          'type' => 'varchar',
          'length' => 255,
        ],
        'name' => [
          'description' => 'The Frontify name.',
          'type' => 'varchar',
          'length' => 255,
        ],
        'metadata' => [
          'description' => 'Arbitrary storage for Frontify metadata (JSON).',
          'type' => 'text',
          'size' => 'normal',
        ],
      ],
      'indexes' => [
        'uri' => [['uri', 30]],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function mainPropertyName(): string {
    return 'uri';
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function isEmpty(): bool {
    $value = $this->get('uri')->getValue();
    return $value === NULL || $value === '';
  }

  public function isExternal(): bool {
    return $this->getUrl()->isExternal();
  }

  public function getUrl(): Url {
    return Url::fromUri($this->uri);
  }

}
