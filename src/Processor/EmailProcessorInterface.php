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

  /**
   * Gets the weight of the email processor.
   *
   * @param int $phase
   *   The phase that will run, one of the EmailInterface::PHASE_ constants.
   *
   * @return int
   *   The weight.
   */
  public function getWeight(int $phase);

}
