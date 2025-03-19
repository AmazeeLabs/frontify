/**
 * @file
 * Provides functionality to handle Frontify Finder's drupal integration.
 */

(function () {
  Drupal.frontifyMediaLibrary = {
    handleFinder(openFrontifyButton) {
      openFrontifyButton.addEventListener('click', async (button) => {
        button.preventDefault();

        if (drupalSettings.Frontify.debug_mode) {
          console.group('🚀 Frontify Media Library: Initialization');
          console.log('🔄 Frontify button clicked, initializing...');
        }

        // Get the wrapper class from the settings or use the default.
        const wrapperClass = drupalSettings.Frontify.wrapper_class || '.frontify-media-library-wrapper';
        const selectAddButtonClass = drupalSettings.Frontify.select_add_button_class || '.add-to-drupal';
        const enableImagePreview = drupalSettings.Frontify.enable_image_preview;
        const hideOpenButton = drupalSettings.Frontify.hide_open_button;
        const enableImageStyles = drupalSettings.Frontify.enable_image_styles;
        const messageElement = drupalSettings.Frontify.message_element || '.frontify-message-information';

        if (drupalSettings.Frontify.debug_mode) {
          console.log('⚙️ Configuration loaded:', {
            wrapperClass,
            selectAddButtonClass,
            enableImagePreview,
            hideOpenButton,
            enableImageStyles,
            messageElement
          });
        }

        // Start looking for the elements.
        const $mediaLibraryWrapper = document.querySelector(wrapperClass);
        const $finderWrapper = $mediaLibraryWrapper.querySelector('.frontify-finder-wrapper');

        // Get the form fields.
        const $frontifyNameField = $mediaLibraryWrapper.querySelector('.js-form-item-name');
        const $frontifyUriField = $mediaLibraryWrapper.querySelector('.js-form-item-uri');
        const $frontifyIdField = $mediaLibraryWrapper.querySelector('.js-form-item-id');
        const $frontifyMetadataField = $mediaLibraryWrapper.querySelector('.js-form-item-metadata');
        const $frontifyImagePreivew = $mediaLibraryWrapper.querySelector('.frontify-image-preview');
        const $frontifyMessageElement = $mediaLibraryWrapper.querySelector(messageElement);

        if (drupalSettings.Frontify.debug_mode) {
          console.log('🔍 DOM elements found:', {
            mediaLibraryWrapper: !!$mediaLibraryWrapper,
            finderWrapper: !!$finderWrapper,
            nameField: !!$frontifyNameField,
            uriField: !!$frontifyUriField,
            idField: !!$frontifyIdField,
            metadataField: !!$frontifyMetadataField,
            imagePreview: !!$frontifyImagePreivew,
            messageElement: !!$frontifyMessageElement
          });
        }

        const isInModal = $mediaLibraryWrapper.closest('#drupal-modal') !== null;

        if (drupalSettings.Frontify.debug_mode) {
          console.log(`🖼️ Display context: ${isInModal ? 'Inside modal' : 'Regular page'}`);
        }

        // Look for an optional field to auto-select the entity browser widget.
        let $frontifyAutoSelect = null;
        if (drupalSettings.Frontify.trigger_element) {
          $frontifyAutoSelect = $mediaLibraryWrapper.querySelector(drupalSettings.Frontify.trigger_element);
          if (drupalSettings.Frontify.debug_mode) {
            console.log(`🎯 Auto-select trigger element: ${$frontifyAutoSelect ? 'Found' : 'Not found'}`);
          }
        }

        // Hide and disable the opener button unless the finder is closed.
        if (hideOpenButton) {
          button.currentTarget.style.display = 'none';
          if (drupalSettings.Frontify.debug_mode) {
            console.log('👁️ Hiding opener button');
          }
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
          if (drupalSettings.Frontify.debug_mode) {
            console.log('🔒 Disabled add to Drupal button until selection');
          }
        }

        // Resize the finder wrapper to fit the modal.
        function resizeFinderWrapper($finderWrapper) {
          const $modal = document.querySelector('#drupal-modal');
          if ($modal) {
            // Account for padding and other elements
            const modalHeight = $modal.clientHeight;
            const padding = 40; // Adjust this value based on your modal's padding
            $finderWrapper.style.height = `${modalHeight - padding}px`;
            if (drupalSettings.Frontify.debug_mode) {
              console.log(`📏 Resized finder wrapper to ${modalHeight - padding}px height`);
            }
          }
        }

        function updateMediaButtonState() {
          if (!$mediaInsertButton) return;

          // Find all checkboxes under the media library rows
          const checkboxes = document.querySelectorAll('.media-library-views-form__rows input[type="checkbox"]');

          // Check if any checkbox is checked
          const anyChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);

          // Update button state
          $mediaInsertButton.disabled = !anyChecked;

          if (drupalSettings.Frontify && drupalSettings.Frontify.debug_mode) {
            console.log(`${anyChecked ? '🔓 Enabled' : '🔒 Disabled'} media insert button in modal`);
          }
        }

        // Add event listeners to checkboxes
        function setupCheckboxListeners() {
          const checkboxesContainer = document.querySelector('.media-library-views-form__rows');

          if (!checkboxesContainer) return;

          // Add event delegation to handle both existing and future checkboxes
          checkboxesContainer.addEventListener('change', function(event) {
            if (event.target.type === 'checkbox') {
              updateMediaButtonState();
            }
          });

          // Initial state setup
          updateMediaButtonState();

          if (drupalSettings.Frontify && drupalSettings.Frontify.debug_mode) {
            console.log('📋 Set up checkbox change listener on media library rows');
          }
        }

        let $mediaInsertButton = null;
        if (isInModal) {
          $mediaInsertButton = document.querySelector('.ui-dialog-buttonset .media-library-select');
          if ($mediaInsertButton) {
            $mediaInsertButton.disabled = true;
            if (drupalSettings.Frontify.debug_mode) {
              console.log('🔒 Disabled media insert button in modal');
            }

            // Run setup
            setupCheckboxListeners();

            // Set up a MutationObserver to handle cases where the checkboxes container might be added after page load
            const bodyObserver = new MutationObserver(() => {
              if (document.querySelector('.media-library-views-form__rows')) {
                setupCheckboxListeners();
                if (drupalSettings.Frontify && drupalSettings.Frontify.debug_mode) {
                  console.log('👀 Detected media library rows added to DOM');
                }
              }
            });

            bodyObserver.observe(document.body, { childList: true, subtree: true });
          }
          resizeFinderWrapper($finderWrapper);

          // Handle window resize
          const resizeObserver = new ResizeObserver(() => {
            resizeFinderWrapper($finderWrapper);
          });

          // Observe the modal for size changes
          const $modal = document.querySelector('#drupal-modal');
          if ($modal) {
            resizeObserver.observe($modal);
            if (drupalSettings.Frontify.debug_mode) {
              console.log('👀 Observing modal for size changes');
            }
          }
        }

        if (enableImageStyles) {
          $addToDrupalButton.addEventListener('click', (event) => {
            if (drupalSettings.Frontify.debug_mode) {
              console.group('🖌️ Frontify: Image Styles Processing');
              console.log('🔄 Add to Drupal button clicked, processing image styles...');
            }

            $frontifyMessageElement.innerHTML = '<div class="ajax-progress ajax-progress--throbber"><div class="ajax-progress__throbber">&nbsp;</div><div class="ajax-progress__message">' + Drupal.t('Building image styles, please wait...') + '</div></div>';

            // Defer the disabling to allow the form to submit
            setTimeout(() => {
              if ($frontifyAutoSelect) {
                $frontifyAutoSelect.disabled = true;
                if (drupalSettings.Frontify.debug_mode) {
                  console.log('🔒 Disabled auto-select trigger');
                }
              }
              $addToDrupalButton.disabled = true;
              if (drupalSettings.Frontify.debug_mode) {
                console.log('🔒 Disabled add to Drupal button');
              }

              if (drupalSettings.Frontify.debug_mode) {
                console.log('⏳ Image styles generation in progress...');
                console.groupEnd();
              }
            }, 0);
          });
        }

        try {
          if (drupalSettings.Frontify.debug_mode) {
            console.log('⚙️ Creating Frontify Finder with API URL:', drupalSettings.Frontify.api_url);
          }

          // Instantiate the Frontify finder.
          const $finder = await window.FrontifyFinder.create({
            clientId: 'drupal',
            domain: drupalSettings.Frontify.api_url,
            options: {
              allowMultiSelect: false,
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
            console.log('✅ Finder instance created successfully');
          }

          // It's not possible to easily override the Media Library title with
          // the UI builder, also, Gutenberg has its own implementation
          // so change it here.
          const $mediaLibraryTitle = document.querySelector('.ui-dialog-title');
          if ($mediaLibraryTitle) {
            $mediaLibraryTitle.textContent = Drupal.t('Frontify Media Library');
            if (drupalSettings.Frontify.debug_mode) {
              console.log('✏️ Updated media library dialog title');
            }
          }

          // Gutenberg is adding the ui-dialog buttons anytime which
          // can be confusing to be able to Insert when there is nothing
          // to insert yet. So we remove this here.
          const $mediaLibraryButtons = document.querySelectorAll('.ui-dialog-buttonset button');
          if ($mediaLibraryButtons) {
            $mediaLibraryButtons.forEach((button) => {
              if (button.textContent === 'Insert') {
                button.style.display = 'none';
                if (drupalSettings.Frontify.debug_mode) {
                  console.log('🙈 Hiding premature Insert button from Gutenberg');
                }
              }
            });
          }

          // Add listener for assets chosen.
          $finder.onAssetsChosen((assets) => {
            if (drupalSettings.Frontify.debug_mode) {
              console.group('🖼️ Frontify: Asset Selection');
              console.log('📋 Selected Assets:', assets);
            }

            $frontifyUriField.querySelector('input').value = assets[0].previewUrl;
            $frontifyIdField.querySelector('input').value = assets[0].id;
            $frontifyNameField.querySelector('input').value = assets[0].title;
            $frontifyMetadataField.querySelector('textarea').value = JSON.stringify(assets[0]);

            if (drupalSettings.Frontify.debug_mode) {
              console.log('📝 Form fields populated with asset data');
            }

            if (enableImagePreview) {
              const image = document.createElement('img');
              image.src = assets[0].previewUrl;
              image.width = 200;
              const label = document.createElement('label');
              label.textContent = Drupal.t('Image Preview');
              label.classList.add('form-item__label');
              $frontifyImagePreivew.replaceChildren(label, image);

              if (drupalSettings.Frontify.debug_mode) {
                console.log('🖼️ Image preview created');
              }
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
              if (drupalSettings.Frontify.debug_mode) {
                console.log('🔓 Add to Drupal button enabled');
              }
            }

            // Trigger an event to auto-select the entity browser widget.
            if ($frontifyAutoSelect && drupalSettings.Frontify.trigger_event) {
              const event = new Event(drupalSettings.Frontify.trigger_event);
              $frontifyAutoSelect.dispatchEvent(event);
              if (drupalSettings.Frontify.debug_mode) {
                console.log(`🎯 Triggered event "${drupalSettings.Frontify.trigger_event}" on auto-select element`);
              }
            }

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

            $frontifyNameField.style.display = 'none';

            if (hideOpenButton) {
              button.target.style.display = 'block';
              if (drupalSettings.Frontify.debug_mode) {
                console.log('👁️ Showing opener button again');
              }
            }
            button.target.disabled = false;
            $frontifyImagePreivew.style.display = 'none';
            $finderWrapper.style.display = 'none';
            $finderWrapper.replaceChildren();
            $mediaLibraryWrapper.classList.remove('open');

            if ($addToDrupalButton) {
              $addToDrupalButton.disabled = true;
              if (drupalSettings.Frontify.debug_mode) {
                console.log('🔒 Disabled add to Drupal button');
              }
            }

            if (drupalSettings.Frontify.debug_mode) {
              console.log('🧹 Cleanup completed after cancellation');
              console.groupEnd();
            }
          });

          $finderWrapper.style.display = 'flex';
          $finder.mount($finderWrapper);

          if (drupalSettings.Frontify.debug_mode) {
            console.log('🔌 Finder mounted to DOM');
            console.groupEnd();
          }
        } catch (error) {
          if (drupalSettings.Frontify.debug_mode) {
            console.group('❌ Frontify: Error');
            console.error('💥 Error initializing Frontify Finder:', error);
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
            if (drupalSettings.Frontify.debug_mode) {
              console.log('🔓 Re-enabled add to Drupal button');
            }
          }

          if (drupalSettings.Frontify.debug_mode) {
            console.log('🧹 Error recovery completed');
            console.groupEnd();
          }
        }
      });

      // Trigger on initial load.
      if (openFrontifyButton) {
        openFrontifyButton.click();
        if (drupalSettings.Frontify.debug_mode) {
          console.log('🚀 Auto-triggering Frontify button click on load');
        }
      }
    },
  };
})();
