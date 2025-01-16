<?php

namespace Drupal\frontify\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\MessageCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\media\MediaInterface;
use Drupal\media\MediaTypeInterface;
use Drupal\media_library\Ajax\UpdateSelectionCommand;
use Drupal\media_library\Form\AddFormBase;

/**
 * Form to create media entities using a Frontify source plugin.
 */
class FrontifyMediaImageForm extends AddFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->getBaseFormId() . '_media_frontify_image';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // @todo Remove the ID when we can use selectors to replace content via
    //   AJAX in https://www.drupal.org/project/drupal/issues/2821793.
    $form['#prefix'] = '<div id="media-library-add-form-wrapper">';
    $form['#suffix'] = '</div>';

    // The media library is loaded via AJAX, which means that the form action
    // URL defaults to the current URL. However, to add media, we always need to
    // submit the form to the media library URL, not whatever the current URL
    // may be.
    $form['#action'] = Url::fromRoute('media_library.ui', [], [
      'query' => $this->getMediaLibraryState($form_state)->all(),
    ])->toString();

    // The form is posted via AJAX. When there are messages set during the
    // validation or submission of the form, the messages need to be shown to
    // the user.
    $form['status_messages'] = [
      '#type' => 'status_messages',
    ];

    $form['#attributes']['class'] = [
      'js-media-library-add-form',
    ];

    $added_media = $this->getAddedMediaItems($form_state);
    if (empty($added_media)) {
      $form = $this->buildInputElement($form, $form_state);
    }
    else {
      $form['#attributes']['data-input'] = 'true';

      // This deserves to be themeable, but it doesn't need to be its own "real"
      // template.
      $form['description'] = [
        '#type' => 'inline_template',
        '#template' => '<p>{{ text }}</p>',
        '#context' => [
          'text' => $this->formatPlural(count($added_media), 'The Frontify image has been imported but not yet saved. Fill any extra field. Clicking on Insert saves it.', 'The Frontify images have been imported but not yet saved. Fill any extra field. Clicking on Insert saves images.'),
        ],
      ];

      $form['media'] = [
        '#pre_render' => [
          [$this, 'preRenderAddedMedia'],
        ],
        '#attributes' => [
          'class' => [
            // This needs to be focus-able by an AJAX response.
            // @see ::updateFormCallback()
            'js-media-library-add-form-added-media',
          ],
          'aria-label' => $this->t('Added media items'),
          // Add the tabindex '-1' to allow the focus to be shifted to the added
          // media wrapper when items are added. We set focus to the container
          // because a media item does not necessarily have required fields and
          // we do not want to set focus to the remove button automatically.
          // @see ::updateFormCallback()
          'tabindex' => '-1',
        ],
      ];
      foreach ($added_media as $delta => $media) {
        $form['media'][$delta] = $this->buildEntityFormElement($media, $form, $form_state, $delta);
      }

      $form['selection'] = $this->buildCurrentSelectionArea($form, $form_state);
      $form['actions'] = $this->buildActions($form, $form_state);
    }

    // Allow the current selection to be set in a hidden field so the selection
    // can be passed between different states of the form. This field is filled
    // via JavaScript so the default value should be empty.
    // @see Drupal.behaviors.MediaLibraryItemSelection
    $form['current_selection'] = [
      '#type' => 'hidden',
      '#default_value' => '',
      '#attributes' => [
        'class' => [
          'js-media-library-add-form-current-selection',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildInputElement(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\frontify\FrontifyFieldsUi $fontifyFieldUiService */
    $fontifyFieldUiService = \Drupal::service('frontify.fields.ui');

    $fields = $fontifyFieldUiService->mediaLibraryUi();

    if (isset($fields['message'])) {
      return $fields;
    }

    $form = array_merge($form, $fields);

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
      '#validate' => ['::validateUrl'],
      '#attributes' => [
        'class' => ['add-to-drupal'],
      ],
      '#submit' => ['::addButtonSubmit'],
      // @todo check why states api is not working here.
      //   using custom javascript for now.
      //      '#states' => [
      //        'enabled' => [
      //          ':input[name="uri"]' => ['filled' => TRUE],
      //        ],
      //      ],
      '#ajax' => [
        'callback' => '::updateFormCallback',
        'wrapper' => 'media-library-wrapper',
        'url' => Url::fromRoute('media_library.ui'),
        'options' => [
          'query' => $this->getMediaLibraryState($form_state)->all() + [
            FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
          ],
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function addButtonSubmit(array $form, FormStateInterface $form_state) {
    $media_type = $this->getMediaType($form_state);
    $media_storage = $this->entityTypeManager->getStorage('media');
    $source_field_name = $this->getSourceFieldName($media_type);
    $media = $this->createMediaFromValues($media_type, $media_storage, $source_field_name, $form_state);
    $form_state->set('media', array_values([$media]));
    // Save the selected items in the form state, so they are remembered when an
    // item is removed.
    $media = $this->entityTypeManager->getStorage('media')
      ->loadMultiple(explode(',', $form_state->getValue('current_selection')));
    // Any ID can be passed to the form, so we have to check access.
    $form_state->set('current_selection', array_filter($media, function ($media_item) {
      return $media_item->access('view');
    }));
    $form_state->setRebuild();
  }

  /**
   * Replaces createMediaFromValue().
   */
  protected function createMediaFromValues(
    MediaTypeInterface $media_type,
    EntityStorageInterface $media_storage,
    $source_field_name,
    FormStateInterface $form_state
  ) {
    $config = \Drupal::config('frontify.settings');
    $deduplicate = $config->get('media_deduplicate') === 1;
    if ($deduplicate) {
      // Check first if the Media exists, and if so, return it, so
      // we don't end up creating duplicates.
      $media_storage->getQuery()->accessCheck(FALSE);
      $query = $media_storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('bundle', $media_type->id())
        ->condition($source_field_name . '.uri', $form_state->getValue('uri'));
      $media_ids = $query->execute();
      if (!empty($media_ids)) {
        $media_id = reset($media_ids);
        return $media_storage->load($media_id);
      }
    }

    $media = $media_storage->create([
      'bundle' => $media_type->id(),
      $source_field_name => [
        'uri' => $form_state->getValue('uri'),
        'id' => $form_state->getValue('id'),
        'name' => $form_state->getValue('name'),
        'metadata' => $form_state->getValue('metadata'),
      ],
    ]);
    $media->setName($form_state->getValue('name'));
    return $media;
  }

  /**
   * Validates the Frontify URL.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateUrl(array &$form, FormStateInterface $form_state) {
    $media_type = $this->getMediaType($form_state);
    $media_storage = $this->entityTypeManager->getStorage('media');
    $source_field_name = $this->getSourceFieldName($media_type);
    $media = $this->createMediaFromValues($media_type, $media_storage, $source_field_name, $form_state);
    if ($media) {
      $violations = $media->validate();
      if ($violations->count() > 0) {
        /** @var \Symfony\Component\Validator\ConstraintViolation $violation */
        foreach ($violations as $violation) {
          $form_state->setErrorByName('uri', $violation->getMessage());
        }
      }
    }
    else {
      $form_state->setErrorByName('uri', $this->t('Invalid URL.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildActions(array $form, FormStateInterface $form_state) {
    // In the Gutenberg context, we need the save_select action to make it
    // work properly, as the Gutenberg block is not a Drupal widget per se.
    // @todo limit to the Frontify media type.
    if (str_contains($form['#action'], 'gutenberg.media_library.opener')) {
      $actions = [
        '#type' => 'actions',
        'save_select' => [
          '#type' => 'submit',
          '#button_type' => 'primary',
          // Technically, it calls updateLibrary, but we are adding
          // an extra command to trigger the Insert button click there.
          // So we also mean insert here, and so it stays consistent
          // with Drupal Media reference field.
          '#value' => $this->t('Insert'),
          '#ajax' => [
            'callback' => '::updateLibrary',
            'wrapper' => 'media-library-add-form-wrapper',
          ],
        ],
      ];
      return $actions;
    }

    // In the Drupal Media reference field context, we only need
    // the "Save and insert" button action.
    // Rename it to "Insert" and make it the primary button, so it's clearer.
    $actions['save_insert'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Insert'),
      '#ajax' => [
        'callback' => '::updateWidget',
        'wrapper' => 'media-library-add-form-wrapper',
      ],
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function updateLibrary(array &$form, FormStateInterface $form_state) {
    // Override core to add an extra command for Gutenberg,
    // that triggers the Insert button click.
    if ($form_state::hasAnyErrors()) {
      return $form;
    }

    $media_ids = array_map(function (MediaInterface $media) {
      return $media->id();
    }, $this->getAddedMediaItems($form_state));

    $selected_count = $this->getSelectedMediaItemCount($media_ids, $form_state);

    $response = new AjaxResponse();
    $response->addCommand(new UpdateSelectionCommand($media_ids));
    $media_id_to_focus = array_pop($media_ids);
    $response->addCommand(new ReplaceCommand('#media-library-add-form-wrapper', $this->buildMediaLibraryUi($form_state)));
    $response->addCommand(new InvokeCommand("#media-library-content [value=$media_id_to_focus]", 'focus'));
    $available_slots = $this->getMediaLibraryState($form_state)->getAvailableSlots();
    if ($available_slots > 0 && $selected_count > $available_slots) {
      $warning = $this->formatPlural($selected_count - $available_slots, 'There are currently @total items selected. The maximum number of items for the field is @max. Remove @count item from the selection.', 'There are currently @total items selected. The maximum number of items for the field is @max. Remove @count items from the selection.', [
        '@total' => $selected_count,
        '@max' => $available_slots,
      ]);
      $response->addCommand(new MessageCommand($warning, '#media-library-messages', ['type' => 'warning']));
    }
    else {
      // Set visibility-hidden to the Frontify media library.
      $response->addCommand(new InvokeCommand('.frontify-media-library-wrapper', 'addClass', ['visually-hidden']));
      // Trigger a click on the Insert button.
      // Technically, there is no class for the Insert button, so we use both "Insert" and "Close"
      // but it doesn't cause any issue here.
      $response->addCommand(new InvokeCommand('.ui-dialog-buttonset .ui-button', 'click'));
    }

    return $response;
  }

  /**
   * Get the number of selected media.
   *
   * @param array $media_ids
   *   Array with the media IDs.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return int
   *   The number of media currently selected.
   */
  private function getSelectedMediaItemCount(array $media_ids, FormStateInterface $form_state): int {
    $selected_count = count($media_ids);
    if ($current_selection = $form_state->getValue('current_selection')) {
      $selected_count += count(explode(',', $current_selection));
    }
    return $selected_count;
  }

}
