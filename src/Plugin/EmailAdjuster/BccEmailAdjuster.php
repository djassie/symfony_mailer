<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Bcc Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_bcc",
 *   label = @Translation("Bcc"),
 *   description = @Translation("Sets the email bcc header."),
 * )
 */
class BccEmailAdjuster extends AddressAdjusterBase {

  /**
   * {@inheritdoc}
   */
  protected function setAddresses(EmailInterface $email, array $addresses) {
    $email->setBcc(...$addresses);
  }

}
