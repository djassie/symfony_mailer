<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the interface for Email Processors.
 */
interface EmailProcessorInterface {

  /**
   * Runs processing on an email message for a phase.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   */
  public function initialize(EmailInterface $email);

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
