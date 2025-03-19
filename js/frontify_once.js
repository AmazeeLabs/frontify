/**
 * @file
 * Provides additional javascript for managing the frontify drupal.
 */
(function (Drupal, drupalSettings, once) {
  Drupal.behaviors.Frontify = {
    attach(context, settings) {
      if (settings.Frontify && settings.Frontify.debug_mode) {
        console.group('🔌 Frontify: Behavior Attachment');
        console.log(`⏱️ Initialization started at: ${new Date().toISOString()}`);
        console.log('🔍 Context:', context === document ? 'Document' : 'Partial DOM update');
      }

      const elements = once(
        'open-frontify-finder',
        'input.frontify-finder-open-button',
        context
      );

      if (settings.Frontify && settings.Frontify.debug_mode) {
        console.log(`🔢 Found ${elements.length} Frontify finder button(s) to initialize`);
      }

      elements.forEach((el, index) => {
        if (settings.Frontify && settings.Frontify.debug_mode) {
          console.group(`🔘 Processing Frontify button #${index + 1}`);
          console.log('🏷️ Button ID:', el.id || 'No ID');
          console.log('🖥️ Context mode:', settings.Frontify.context);
        }

        switch (settings.Frontify.context) {
          case 'media_library':
            if (settings.Frontify && settings.Frontify.debug_mode) {
              console.log('🏛️ Initializing Media Library mode');
            }
            Drupal.frontifyMediaLibrary.handleFinder(el);
            break;

          case 'entity_form':
            if (settings.Frontify && settings.Frontify.debug_mode) {
              console.log('📝 Initializing Entity Form mode');
            }
            Drupal.frontifyMediaForm.handleFinder(el);
            break;

          default:
            console.error('❌ Frontify: Invalid context mode', settings.Frontify.context);
            if (settings.Frontify && settings.Frontify.debug_mode) {
              console.warn('⚠️ Expected either "media_library" or "entity_form"');
            }
        }

        if (settings.Frontify && settings.Frontify.debug_mode) {
          console.log('✅ Button initialization completed');
          console.groupEnd(); // Close button group
        }
      });

      if (settings.Frontify && settings.Frontify.debug_mode && elements.length === 0) {
        console.log('ℹ️ No Frontify buttons found in this context');
      }

      if (settings.Frontify && settings.Frontify.debug_mode) {
        console.log('✅ Frontify behavior attachment completed');
        console.groupEnd(); // Close main group
      }
    },
  };
})(Drupal, drupalSettings, once);
