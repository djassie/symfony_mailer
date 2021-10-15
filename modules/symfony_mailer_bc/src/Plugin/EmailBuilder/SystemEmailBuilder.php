<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderInterface;
use Drupal\symfony_mailer\Email;

/**
 * Defines the Email Builder plug-in for system module.
 *
 * @EmailBuilder(
 *   id = "system",
 *   label = @Translation("Email Builder for system module"),
 * )
 */
class SystemEmailBuilder implements EmailBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function build(Email $email) {
    $params = $email->getParams();
    $context = $params['context'];
    $content = [
      '#type' => 'processed_text',
      '#text' => $context['message'],
    ];

    $email->subject($context['subject'])
      ->content($content)
      ->addParam('token_options', $context);
  }

}
