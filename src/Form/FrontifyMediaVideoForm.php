<?php

namespace Drupal\frontify\Form;

/**
 * Form to create media entities using a Frontify source plugin.
 */
class FrontifyMediaVideoForm extends FrontifyMediaFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->getBaseFormId() . '_media_frontify_video';
  }

}
