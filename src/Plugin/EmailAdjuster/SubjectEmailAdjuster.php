<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Subject header Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_subject",
 *   label = @Translation("Subject"),
 *   description = @Translation("Sets the email subject."),
 * )
 */
class SubjectEmailAdjuster extends EmailAdjusterBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $subject = $this->configuration['value'];

    if ($variables = $email->getVariables()) {
      // Apply TWIG template
      $subject = [
        '#type' => 'inline_template',
        '#template' => $subject,
        '#context' => $variables,
      ];
      $renderer = \Drupal::service('renderer');
      $subject = $renderer->renderPlain($subject);
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
      '#description' => $this->t('Email subject. This field may support tokens or Twig template syntax – please check the supplied default policy for possible values.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->configuration['value'];
  }

}
