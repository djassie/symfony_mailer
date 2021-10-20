<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for system module.
 *
 * @EmailBuilder(
 *   id = "system",
 *   label = @Translation("Email Builder for system module"),
 * )
 */
class SystemEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $params = $email->getParams();
    $context = $params['context'];
    $body = [
      '#type' => 'processed_text',
      '#text' => $context['message'],
    ];

    $email->setSubject($context['subject'])
      ->setBody($body)
      ->addParam('token_options', $context);
  }

}
