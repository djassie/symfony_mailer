<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailProcessorBase;
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
class SubjectEmailAdjuster extends EmailProcessorBase {

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

}
