<?php

declare(strict_types=1);

namespace Drupal\frontify\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\frontify\Plugin\Field\FieldType\FrontifyAssetField;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the Frontify Asset ID constraint.
 *
 * Media entities from the same media type should have unique Frontify id.
 *
 * Applies only when the deduplicate configuration is enabled in the media type.
 */
final class FrontifyAssetIdConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * Constructs the object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $value, Constraint $constraint): void {
    if (!$constraint instanceof FrontifyAssetIdConstraint) {
      throw new UnexpectedTypeException($constraint, FrontifyAssetIdConstraint::class);
    }

    if (!$value instanceof FrontifyAssetField) {
      throw new \InvalidArgumentException(
        sprintf('The validated value must be instance of \Drupal\frontify\Plugin\Field\FieldType\FrontifyAssetField, %s was given.', get_debug_type($value))
      );
    }

    $media = $value->getEntity();
    if (!$media instanceof MediaInterface) {
      return;
    }

    // Do not trigger this constraint validation in the Media Library UI
    // context, as we are using the Frontify Finder there, that already
    // have its own deduplication handler.
    // Also, this would prevent it to get through the initial import
    // if deduplication is enabled.
    if (\Drupal::routeMatch()->getRouteName() === 'media_library.ui') {
      return;
    }

    /** @var \Drupal\media\MediaTypeInterface $media_type */
    $media_type = $this->entityTypeManager
      ->getStorage('media_type')->load($media->bundle());

    $media_type_configuration = $media_type->getSource()->getConfiguration();
    $deduplicate = !empty($media_type_configuration['deduplicate']) &&
      $media_type_configuration['deduplicate'] === 1;
    if (!$deduplicate) {
      return;
    }

    $source_field_name = $media_type
      ->getSource()->getSourceFieldDefinition($media_type)->getName();
    $media_storage = $this->entityTypeManager->getStorage('media');

    $media_storage->getQuery()->accessCheck(FALSE);
    $query = $media_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('bundle', $media->bundle())
      ->condition($source_field_name . '.id', $value->id);
    $media_ids = $query->execute();

    if (!empty($media_ids)) {
      // Unset the current media if we edit an existing one.
      if (!$media->isNew() && array_key_exists($media->id(), $media_ids)) {
        unset($media_ids[$media->id()]);
      }

      if (!empty($media_ids)) {
        $media_id = reset($media_ids);
        $media = $media_storage->load($media_id);
        $media_url = $media->toUrl()->toString();
        $this->context->addViolation($constraint->message, [
          '@id' => $value->id,
          ':duplicate_media_url' => $media_url,
        ]);
      }
    }
  }

}
