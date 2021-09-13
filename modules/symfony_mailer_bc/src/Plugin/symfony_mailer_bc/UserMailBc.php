<?php

namespace Drupal\symfony_mailer_bc\Plugin\symfony_mailer_bc;

use Drupal\symfony_mailer_bc\MailBcInterface;

/**
 * Defines the mail back-compatibility plug-in for user module.
 *
 * @MailBc(
 *   id = "user",
 *   label = @Translation("Mail BC for user module"),
 * )
 */
class UserMailBc implements MailBcInterface {

  /**
   * {@inheritdoc}
   */
  public function mail($email, $key, $to, $langcode, $params) {
    $mail_config = \Drupal::config('user.mail');
    $subject = $mail_config->get("$key.subject");
    $content = [
      '#type' => 'processed_text',
      '#text' => $mail_config->get("$key.body"),
      '#format' => $mail_config->get('text_format'),
    ];
    $data = ['user' => $params['account']];
    $token_options = ['langcode' => $langcode, 'callback' => 'user_mail_tokens', 'clear' => TRUE];

    $email->subject($subject)
      ->content($content)
      ->data($data)
      ->enableTokenReplace($token_options);
  }

}
