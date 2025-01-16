/**
 * @file
 * Provides the base functionality to handle Frontify Finder's drupal integration.
 */

(function () {
  Drupal.frontifyMediaForm = {
    handleFinder(el) {
      el.addEventListener('click', async element => {
        element.currentTarget.disabled = true;
        // Handle unlimited fields.
        const $field = el.closest('.field--widget-frontify-asset-field-widget');
        let $fieldItem = '';
        // It's a single field.
        if (!el.closest('tr')) {
          $fieldItem = el.closest('.field--widget-frontify-asset-field-widget');
        }
        // Unlimited field.
        else {
          $fieldItem = el.closest('tr');
        }
        $field
          .querySelector('.frontify-wrapper-finder-overlay')
          .classList.add('frontify-wrapper-finder-overlay-shown');
        const $wrapper = $field.querySelector('.frontify-finder-wrapper');
        try {
          // Create and Authorize the Finder.
          const $finder = await window.FrontifyFinder.create({
            clientId: 'drupal',
            domain: drupalSettings.Frontify.api_url,
            options: {
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
          if (drupalSettings.Frontify.debug_mode) {
            console.log($finder);
          }
          // Add listener for assets chosen.
          $finder.onAssetsChosen(assets => {
            if (drupalSettings.Frontify.debug_mode) {
              console.log(assets);
            }
            $fieldItem.querySelector('input.frontify-asset-link-url').value =
              assets[0].previewUrl;
            $fieldItem
              .querySelector('img.frontify-image-preview')
              .setAttribute('src', assets[0].previewUrl);
            $fieldItem.querySelector('input.frontify-asset-id').value =
              assets[0].id;
            $fieldItem.querySelector('input.frontify-asset-name').value =
              assets[0].title;
            $fieldItem.querySelector('textarea.frontify-asset-metadata').value =
              JSON.stringify(assets[0]);

            // Media name
            document.querySelector('#edit-name-wrapper input').value = assets[0].title;

            $field
              .querySelector('.frontify-wrapper-finder-overlay')
              .classList.remove('frontify-wrapper-finder-overlay-shown');
            element.target.disabled = false;
            $wrapper.style.display = 'none';
            $wrapper.replaceChildren();
          });

          // Add listener for user abortion.
          $finder.onCancel(() => {
            if (drupalSettings.Frontify.debug_mode) {
              console.log('Selection cancelled!');
            }
            $field
              .querySelector('.frontify-wrapper-finder-overlay')
              .classList.remove('frontify-wrapper-finder-overlay-shown');
            element.target.disabled = false;
            $wrapper.style.display = 'none';
            $wrapper.replaceChildren();
          });

          $wrapper.style.display = 'flex';
          $finder.mount($wrapper);
        } catch (error) {
          if (drupalSettings.Frontify.debug_mode) {
            console.log(error);
          }
          // Re-enable the submit button.
          element.target.disabled = false;
        }
      });
    },
  };
})();
