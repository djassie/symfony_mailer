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

    $email->setSubject($subject)
      ->setBody($content)
      ->setParams($params);
  }

}
