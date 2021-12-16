<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Email Builder plug-in for system module.
 *
 * @EmailBuilder(
 *   id = "system",
 *   sub_types = { "action_send_email" = @Translation("Send mail") },
 * )
 */
class SystemEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    $body = [
      '#type' => 'processed_text',
      '#text' => $email->getParam('message'),
    ];

    $email->setSubject($email->getParam('subject'))
      ->setBody($body)
      ->addProcessor('mailer_token_replace');
  }

}
