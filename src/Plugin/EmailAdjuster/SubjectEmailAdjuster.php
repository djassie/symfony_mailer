<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\ConfigurableAdjusterBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Subject header Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_subject",
 *   label = @Translation("Subject"),
 *   description = @Translation("Sets the email subject."),
 * )
 */
class SubjectEmailAdjuster extends ConfigurableAdjusterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(UnrenderedEmailInterface $email) {
    $subject = $this->configuration['value'];

    if ($variables = $email->getVariables()) {
      // Apply TWIG template
      $subject = [
        '#type' => 'inline_template',
        '#template' => $subject,
        '#context' => $variables,
      ];
    }

    $email->setSubject($subject);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['value'] = [
      '#type' => 'textfield',
      '#default_value' => $this->configuration['value'] ?? NULL,
      '#required' => TRUE,
      '#description' => t('Email subject.'),
    ];

    return $form;
  }

}
