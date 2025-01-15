/**
 * @file
 * Provides the base functionality to handle Frontify Finder's drupal integration.
 */

(function () {
  Drupal.frontifyMediaLibrary = {
    handleFinder(openFrontifyButton) {
      openFrontifyButton.addEventListener('click', async (button) => {
        const $mediaLibraryWrapper = document.querySelector('.frontify-media-library-wrapper');
        const $finderWrapper = $mediaLibraryWrapper.querySelector('.frontify-finder-wrapper');

        const $frontifyNameField = $mediaLibraryWrapper.querySelector('.js-form-item-name');
        const $frontifyUriField = $mediaLibraryWrapper.querySelector('.js-form-item-uri');
        const $frontifyIdField = $mediaLibraryWrapper.querySelector('.js-form-item-id');
        const $frontifyMetadataField = $mediaLibraryWrapper.querySelector('.js-form-item-metadata');

        // Hide and disable the opener button unless the finder is closed.
        button.currentTarget.style.display = 'none';
        button.currentTarget.disabled = true;

        // Hide the name field until it's filled.
        $frontifyNameField.style.display = 'none';

        // Drupal states are not working out, so we disable the add
        // button here until a selection is done in the finder.
        const $addToDrupalButton = $mediaLibraryWrapper.querySelector('.add-to-drupal');
        $addToDrupalButton.disabled = true;

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
              console.log('Button', button);
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

            $frontifyNameField.style.display = 'block';

            $addToDrupalButton.disabled = false;
            button.target.style.display = 'none';
            button.target.disabled = false;
            $finderWrapper.style.display = 'none';
            $finderWrapper.replaceChildren();
          });

          // Add listener for user abortion.
          $finder.onCancel(() => {
            if (drupalSettings.Frontify.debug_mode) {
              console.log('Frontify', 'Selection cancelled');
            }

            $frontifyNameField.style.display = 'none';

            $addToDrupalButton.disabled = true;
            button.target.style.display = 'block';
            button.target.disabled = false;
            $finderWrapper.style.display = 'none';
            $finderWrapper.replaceChildren();
          });

          $finderWrapper.style.display = 'flex';
          $finder.mount($finderWrapper);
        } catch (error) {
          if (drupalSettings.Frontify.debug_mode) {
            console.log('Frontify', error);
          }
          // Re-enable the submit button and the input field.
          $frontifyNameField.style.display = 'block';
          $addToDrupalButton.disabled = false;
          button.target.style.display = 'none';
          button.target.disabled = false;
        }
      });
      // Trigger on initial load.
      if (openFrontifyButton) {
        openFrontifyButton.click();
      }
    },
  };
})();
