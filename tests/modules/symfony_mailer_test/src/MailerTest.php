<?php

namespace Drupal\symfony_mailer_test;

use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Processor\EmailProcessorCustomBase;

/**
 * Tracks sent emails for testing.
 */
class MailerTest extends EmailProcessorCustomBase {

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
    $email->setTransportDsn('null://default');
  }

  /**
   * {@inheritdoc}
   */
  public function postSend(EmailInterface $email) {
    $emails = \Drupal::state()->get('mailer_test.emails', []);
    $emails[] = $email;
    \Drupal::state()->set('mailer_test.emails', $emails);
  }

  /**
   * {@inheritdoc}
   */
  protected function getWeight(int $phase) {
    return 10000;
  }

}
