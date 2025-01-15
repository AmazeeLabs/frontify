/**
 * @file
 * Provides additional javascript for managing the frontify drupal.
 */
(function (Drupal, drupalSettings, once) {
  Drupal.behaviors.Frontify = {
    attach(context, settings) {
      once(
        'open-frontify-finder',
        'input.frontify-finder-open-button',
      ).forEach(el => {
        switch (settings.Frontify.context) {
          case 'media_library':
            Drupal.frontifyMediaLibrary.handleFinder(el);
            break;
          case 'media_form':
            Drupal.frontifyMediaForm.handleFinder(el);
            break;
          default:
            console.error('Frontify context not found');
        }
      });
    },
  };
})(Drupal, drupalSettings, once);
