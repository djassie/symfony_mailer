<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Subject header Email Builder.
 *
 * @EmailBuilder(
 *   id = "email_subject",
 *   label = @Translation("Subject"),
 *   description = @Translation("Sets the email subject."),
 * )
 */
class SubjectEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
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
