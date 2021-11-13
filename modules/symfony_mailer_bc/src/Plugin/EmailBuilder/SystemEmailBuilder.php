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
 *   sub_types = { "action_send_email" },
 * )
 */
class SystemEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $body = [
      '#type' => 'processed_text',
      '#text' => $email->getParam('message'),
    ];

    $email->setSubject($email->getParam('subject'))
      ->setBody($body)
      ->addBuilder('token_replace');
  }

}
