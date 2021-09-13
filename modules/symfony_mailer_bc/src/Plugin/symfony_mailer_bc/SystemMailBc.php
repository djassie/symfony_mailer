<?php

namespace Drupal\symfony_mailer_bc\Plugin\symfony_mailer_bc;

use Drupal\symfony_mailer_bc\MailBcInterface;

/**
 * Defines the mail back-compatibility plug-in for system module.
 *
 * @MailBc(
 *   id = "system",
 *   label = @Translation("Mail BC for system module"),
 * )
 */
class SystemMailBc implements MailBcInterface {

  /**
   * {@inheritdoc}
   */
  public function mail($email, $key, $to, $langcode, $params) {
    $context = $params['context'];
    $content = [
      '#type' => 'processed_text',
      '#text' => $context['message'],
    ];

    $email->subject($context['subject'])
      ->content($content)
      ->enableTokenReplace($params['context']);
  }

}
