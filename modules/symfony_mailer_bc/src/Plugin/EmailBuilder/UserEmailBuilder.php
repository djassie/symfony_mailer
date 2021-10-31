<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "user",
 *   label = @Translation("Email Builder for user module"),
 * )
 */
class UserEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $key = $email->getSubType();
    $mail_config = \Drupal::config('user.mail');
    $body = [
      '#type' => 'processed_text',
      '#text' => $mail_config->get("$key.body"),
      '#format' => $mail_config->get('text_format'),
    ];
    $token_data = ['user' => $email->getParam('account')];
    $token_options = ['callback' => 'user_mail_tokens', 'clear' => TRUE];

    $email->setSubject($mail_config->get("$key.subject"))
      ->setBody($body)
      ->addBuilder('token_replace', ['data' => $token_data, 'options' => $token_options]);
  }

}
