/**
 * @file
 * Provides functionality to handle Frontify Finder's drupal integration.
 */

(function () {
  Drupal.frontifyMediaLibrary = {
    handleFinder(openFrontifyButton) {
      openFrontifyButton.addEventListener('click', async (button) => {
        button.preventDefault();

        // Get the wrapper class from the settings or use the default.
        const wrapperClass = drupalSettings.Frontify.wrapper_class || '.frontify-media-library-wrapper';
        const selectAddButtonClass = drupalSettings.Frontify.select_add_button_class || '.add-to-drupal';
        const enableImagePreview = drupalSettings.Frontify.enable_image_preview;
        const hideOpenButton = drupalSettings.Frontify.hide_open_button;
        const enableImageStyles = drupalSettings.Frontify.enable_image_styles;

        // Start looking for the elements.
        const $mediaLibraryWrapper = document.querySelector(wrapperClass);
        const $finderWrapper = $mediaLibraryWrapper.querySelector('.frontify-finder-wrapper');

        // Get the form fields.
        const $frontifyNameField = $mediaLibraryWrapper.querySelector('.js-form-item-name');
        const $frontifyUriField = $mediaLibraryWrapper.querySelector('.js-form-item-uri');
        const $frontifyIdField = $mediaLibraryWrapper.querySelector('.js-form-item-id');
        const $frontifyMetadataField = $mediaLibraryWrapper.querySelector('.js-form-item-metadata');
        const $frontifyImagePreivew = $mediaLibraryWrapper.querySelector('.frontify-image-preview');

        // Look for an optional field to auto-select the entity browser widget.
        let $frontifyAutoSelect = null;
        if (drupalSettings.Frontify.trigger_element) {
          $frontifyAutoSelect = $mediaLibraryWrapper.querySelector(drupalSettings.Frontify.trigger_element);
        }

        // Hide and disable the opener button unless the finder is closed.
        if (hideOpenButton) {
          button.currentTarget.style.display = 'none';
        }
        button.currentTarget.disabled = true;

        // Hide the name field until it's filled.
        $frontifyNameField.style.display = 'none';
        $frontifyImagePreivew.style.display = 'none';

        $mediaLibraryWrapper.classList.add('open');

        // Drupal states are not working out, so we disable the add
        // button here until a selection is done in the finder.
        const $addToDrupalButton = $mediaLibraryWrapper.querySelector(selectAddButtonClass);
        if ($addToDrupalButton) {
          $addToDrupalButton.disabled = true;
        }

        if (enableImageStyles) {
          $addToDrupalButton.addEventListener('click', (event) => {
            const processingMessage = '<div class="ajax-progress ajax-progress--throbber"><div class="ajax-progress__throbber">&nbsp;</div><div class="ajax-progress__message">' + Drupal.t('Building image styles, please wait...') + '</div></div>';
            $frontifyAutoSelect.insertAdjacentHTML('afterend', processingMessage);

            // Defer the disabling to allow the form to submit
            setTimeout(() => {
              if ($frontifyAutoSelect) {
                $frontifyAutoSelect.disabled = true;
              }
              $addToDrupalButton.disabled = true;
            }, 0);
          });
        }

        try {
          // Instantiate the Frontify finder.
          const $finder = await window.FrontifyFinder.create({
            clientId: 'drupal',
            domain: drupalSettings.Frontify.api_url,
            options: {
              allowMultiSelect: false,
              permanentDownloadUrls: true,
              // @todo configure extensions based on the media bundle.
              //   this should be done as a third party setting of the Media provider
              //   and selected based on its type.
              filters: [
                {
                  key: 'ext',
                  values: [
                    //'ai',
                    //'bmp',
                    //'eps',
                    'gif',
                    //'heif',
                    'jpeg',
                    'jpg',
                    'png',
                    'svg',
                    //'tif',
                    'tiff',
                    'webp',
                  ],
                  inverted: false,
                },
              ],
            },
          });

          // It's not possible to easily override the Media Library title with
          // the UI builder, also, Gutenberg has its own implementation
          // so change it here.
          const $mediaLibraryTitle = document.querySelector('.ui-dialog-title');
          if ($mediaLibraryTitle) {
            $mediaLibraryTitle.textContent = Drupal.t('Frontify Media Library');
          }

          // Gutenberg is adding the ui-dialog buttons anytime which
          // can be confusing to be able to Insert when there is nothing
          // to insert yet. So we remove this here.
          const $mediaLibraryButtons = document.querySelectorAll('.ui-dialog-buttonset button');
          if ($mediaLibraryButtons) {
            $mediaLibraryButtons.forEach((button) => {
              if (button.textContent === 'Insert') {
                button.style.display = 'none';
              }
            });
          }

          // Add listener for assets chosen.
          $finder.onAssetsChosen((assets) => {
            if (drupalSettings.Frontify.debug_mode) {
              console.log('Frontify', assets);
            }
            $frontifyUriField.querySelector('input').value = assets[0].previewUrl;
            $frontifyIdField.querySelector('input').value = assets[0].id;
            $frontifyNameField.querySelector('input').value = assets[0].title;
            $frontifyMetadataField.querySelector('textarea').value = JSON.stringify(assets[0]);

            if (enableImagePreview) {
              const image = document.createElement('img');
              image.src = assets[0].previewUrl;
              image.width = 200;
              const label = document.createElement('label');
              label.textContent = Drupal.t('Image Preview');
              label.classList.add('form-item__label');
              $frontifyImagePreivew.replaceChildren(label, image);
            }
            $frontifyImagePreivew.style.display = 'block';

            $frontifyNameField.style.display = 'block';
            if (hideOpenButton) {
              button.target.style.display = 'none';
            }
            button.target.disabled = false;
            $finderWrapper.style.display = 'none';
            $finderWrapper.replaceChildren();
            $mediaLibraryWrapper.classList.remove('open');
            if ($addToDrupalButton) {
              $addToDrupalButton.disabled = false;
            }

            // Trigger an event to auto-select the entity browser widget.
            if ($frontifyAutoSelect && drupalSettings.Frontify.trigger_event) {
              const event = new Event(drupalSettings.Frontify.trigger_event);
              $frontifyAutoSelect.dispatchEvent(event);
            }
          });

          // Add listener for user abortion.
          $finder.onCancel(() => {
            if (drupalSettings.Frontify.debug_mode) {
              console.log('Frontify', 'Selection cancelled');
            }

            $frontifyNameField.style.display = 'none';

            if (hideOpenButton) {
              button.target.style.display = 'block';
            }
            button.target.disabled = false;
            $frontifyImagePreivew.style.display = 'none';
            $finderWrapper.style.display = 'none';
            $finderWrapper.replaceChildren();
            $mediaLibraryWrapper.classList.remove('open');
            if ($addToDrupalButton) {
              $addToDrupalButton.disabled = true;
            }
          });

          $finderWrapper.style.display = 'flex';
          $finder.mount($finderWrapper);
        } catch (error) {
          if (drupalSettings.Frontify.debug_mode) {
            console.log('Frontify', error);
          }
          // Re-enable the submit button and the input field.
          $frontifyNameField.style.display = 'block';
          $frontifyImagePreivew.style.display = 'none';
          if (hideOpenButton) {
            button.target.style.display = 'none';
          }
          button.target.disabled = false;
          if ($addToDrupalButton) {
            $addToDrupalButton.disabled = false;
          }
        }
      });
      // Trigger on initial load.
      if (openFrontifyButton) {
        openFrontifyButton.click();
      }
    },
  };
})();
