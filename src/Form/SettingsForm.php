<?php

namespace Drupal\frontify\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Frontify settings form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'frontify_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('frontify.settings');

    $form['api'] = [
      '#type' => 'details',
      '#title' => $this->t('API'),
      '#open' => TRUE,
    ];
    $form['api']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('URL'),
      '#default_value' => $config->get('frontify_api_url'),
      '#required' => TRUE,
      '#description' => $this->t('Url must be like https://test.frontify.com.'),
    ];
    $form['api']['token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token'),
      '#default_value' => $config->get('frontify_api_token'),
      '#required' => FALSE,
      '#description' => $this->t('Only needed when interacting with the GraphQL API https://developer.frontify.com/document/2570#/introduction/graphql-api'),
    ];
    $form['api']['beta'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use beta version'),
      '#description' => $this->t('Get access early to new features (might be subject to change, use wisely). Injects the <code>X-Frontify-Beta: enabled</code> header.'),
      '#default_value' => $config->get('frontify_api_beta'),
      '#states' => [
        'visible' => [
          'input[name="token"]' => ['filled' => TRUE],
        ],
      ],
    ];

    $form['debug_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Debug'),
      '#open' => FALSE,
    ];
    $form['debug_settings']['debug_mode'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable debug mode'),
      '#description' => $this->t('Displays console.log() and Media debug information.'),
      '#default_value' => $config->get('debug_mode'),
    ];

    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $config = $this->config('frontify.settings');
    $config->set('frontify_api_url', $form_state->getValue('url'));
    $config->set('frontify_api_token', $form_state->getValue('token'));
    $config->set('frontify_api_beta', $form_state->getValue('beta'));
    $config->set('debug_mode', $form_state->getValue('debug_mode'));

    $config->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return [
      'frontify.settings',
    ];
  }

}
