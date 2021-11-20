<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\ConfigurableAdjusterBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Symfony\Component\Mime\Address;

/**
 * Defines the From Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_from",
 *   label = @Translation("From header"),
 *   description = @Translation("Sets the email from header."),
 * )
 */
class FromEmailAdjuster extends ConfigurableAdjusterBase {
  // @todo Extend from AddressBuilderBase, adding others for cc, Bcc, To, etc.
  // @todo Allow multiple values
  // @todo Setting whether to replace existing addresses or add to them.

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    $mail = $this->configuration['value'];
    $display = $this->configuration['display'];
    $from = $display ? new Address($mail, $display) : $mail;
    $email->getInner()->from($from);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => t('Address'),
      '#default_value' => $this->configuration['value'] ?? NULL,
      '#required' => TRUE,
      '#description' => t('Email address.'),
    ];

    $form['display'] = [
      '#type' => 'textfield',
      '#title' => t('Display name'),
      '#default_value' => $this->configuration['display'] ?? NULL,
      '#description' => t('Human-readable display name.'),
    ];

    return $form;
  }

}
