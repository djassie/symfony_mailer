<?php

namespace Drupal\symfony_mailer_bc\Plugin\MailBuilder;

use Drupal\symfony_mailer\MailBuilderInterface;
use Drupal\symfony_mailer\Email;

/**
 * Defines the Mail Builder plug-in for user module.
 *
 * @MailBuilder(
 *   id = "user",
 *   label = @Translation("Mail Builder for user module"),
 * )
 */
class UserMailBuilder implements MailBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function build(Email $email) {
    $key = $email->getKey()[1];
    $mail_config = \Drupal::config('user.mail');
    $subject = $mail_config->get("$key.subject");
    $content = [
      '#type' => 'processed_text',
      '#text' => $mail_config->get("$key.body"),
      '#format' => $mail_config->get('text_format'),
    ];
    $token_options = ['callback' => 'user_mail_tokens', 'clear' => TRUE];
    $params = ['user' => $email->getParam('account'), 'token_options' => $token_options];

    $email->subject($subject)
      ->content($content)
      ->params($params);
  }

}
