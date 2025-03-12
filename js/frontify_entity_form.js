/**
 * @file
 * Provides the base functionality to handle Frontify Finder's drupal integration.
 */

(function () {
  Drupal.frontifyMediaForm = {
    handleFinder(el) {
      el.addEventListener('click', async element => {
        if (drupalSettings.Frontify.debug_mode) {
          console.group('🚀 Frontify: Initializing Finder');
          console.log('🔍 Click event triggered on:', element.currentTarget);
        }

        element.currentTarget.disabled = true;
        // Handle unlimited fields.
        const $field = el.closest('.field--widget-frontify-asset-field-widget');
        let $fieldItem = '';

        // It's a single field.
        if (!el.closest('tr')) {
          $fieldItem = el.closest('.field--widget-frontify-asset-field-widget');
          if (drupalSettings.Frontify.debug_mode) {
            console.log('📄 Field type: Single field');
          }
        }
        // Unlimited field.
        else {
          $fieldItem = el.closest('tr');
          if (drupalSettings.Frontify.debug_mode) {
            console.log('📑 Field type: Unlimited field');
          }
        }

        $field
          .querySelector('.frontify-wrapper-finder-overlay')
          .classList.add('frontify-wrapper-finder-overlay-shown');

        const $wrapper = $field.querySelector('.frontify-finder-wrapper');

        if (drupalSettings.Frontify.debug_mode) {
          console.log('🔄 Showing overlay and preparing wrapper');
        }

        try {
          if (drupalSettings.Frontify.debug_mode) {
            console.log('⚙️ Creating Frontify Finder with API URL:', drupalSettings.Frontify.api_url);
          }

          // Create and Authorize the Finder.
          const $finder = await window.FrontifyFinder.create({
            clientId: 'drupal',
            domain: drupalSettings.Frontify.api_url,
            options: {
              permanentDownloadUrls: true,
              filters: [
                {
                  key: 'ext',
                  values: [
                    'gif',
                    'jpeg',
                    'jpg',
                    'png',
                    'svg',
                    'tiff',
                    'webp',
                  ],
                  inverted: false,
                },
              ],
            },
          });

          if (drupalSettings.Frontify.debug_mode) {
            console.log('✅ Finder instance created successfully:', $finder);
          }

          // Add listener for assets chosen.
          $finder.onAssetsChosen(assets => {
            if (drupalSettings.Frontify.debug_mode) {
              console.group('🖼️ Frontify: Asset Selection');
              console.log('📋 Selected Assets:', assets);
            }

            $fieldItem.querySelector('input.frontify-asset-link-url').value = assets[0].previewUrl;
            $fieldItem
              .querySelector('img.frontify-image-preview')
              .setAttribute('src', assets[0].previewUrl + '?width=' + drupalSettings.Frontify.preview_image_width);
            $fieldItem.querySelector('input.frontify-asset-id').value = assets[0].id;
            $fieldItem.querySelector('input.frontify-asset-name').value = assets[0].title;
            $fieldItem.querySelector('textarea.frontify-asset-metadata').value = JSON.stringify(assets[0]);

            if (drupalSettings.Frontify.debug_mode) {
              console.log('🏷️ Parent Entity Type:', drupalSettings.Frontify.parent_entity_type_id);
            }

            // Media specifics: set the Media name from the Frontify one.
            if (drupalSettings.Frontify.parent_entity_type_id === 'media') {
              const $mediaName = document.querySelector('[data-drupal-selector="edit-name-wrapper"] input');
              if ($mediaName) {
                $mediaName.value = assets[0].title;
                if (drupalSettings.Frontify.debug_mode) {
                  console.log('✅ Media name set to:', assets[0].title);
                }
              } else {
                if (drupalSettings.Frontify.debug_mode) {
                  console.warn('⚠️ Could not find the media name field.');
                } else {
                  console.log('Could not find the media name field.');
                }
              }
            }

            $field
              .querySelector('.frontify-wrapper-finder-overlay')
              .classList.remove('frontify-wrapper-finder-overlay-shown');
            element.target.disabled = false;
            $wrapper.style.display = 'none';
            $wrapper.replaceChildren();

            if (drupalSettings.Frontify.debug_mode) {
              console.log('✅ Asset selection process completed');
              console.groupEnd();
            }
          });

          // Add listener for user abortion.
          $finder.onCancel(() => {
            if (drupalSettings.Frontify.debug_mode) {
              console.group('🚫 Frontify: Selection Cancelled');
              console.log('👤 User cancelled the asset selection process');
            }

            $field
              .querySelector('.frontify-wrapper-finder-overlay')
              .classList.remove('frontify-wrapper-finder-overlay-shown');
            element.target.disabled = false;
            $wrapper.style.display = 'none';
            $wrapper.replaceChildren();

            if (drupalSettings.Frontify.debug_mode) {
              console.log('🧹 Cleaned up UI elements');
              console.groupEnd();
            }
          });

          $wrapper.style.display = 'flex';
          $finder.mount($wrapper);

          if (drupalSettings.Frontify.debug_mode) {
            console.log('🔌 Finder mounted to DOM');
            console.groupEnd();
          }

        } catch (error) {
          if (drupalSettings.Frontify.debug_mode) {
            console.group('❌ Frontify: Error');
            console.error('💥 Error initializing Frontify Finder:', error);
            console.groupEnd();
          }
          // Re-enable the submit button.
          element.target.disabled = false;
        }
      });
    },
  };
})();
