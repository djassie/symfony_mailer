<?php

namespace Drupal\symfony_mailer_bc\Plugin\MailBuilder;

use Drupal\symfony_mailer\MailBuilderInterface;

/**
 * Defines the Mail Builder plug-in for system module.
 *
 * @MailBuilder(
 *   id = "system",
 *   label = @Translation("Mail Builder for system module"),
 * )
 */
class SystemMailBuilder implements MailBuilderInterface {

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
      ->addParam('token_options', $params['context']);
  }

}
