<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the interface for Email Processors.
 */
interface EmailProcessorInterface {

  /**
   * Initializes an email to call this email processor.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to initialize.
   */
  public function init(EmailInterface $email);

}
