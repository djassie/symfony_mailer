<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Reply-to Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_reply_to",
 *   label = @Translation("Reply-to"),
 *   description = @Translation("Sets the email reply-to header."),
 * )
 */
class ReplyToEmailAdjuster extends AddressAdjusterBase {

  /**
   * {@inheritdoc}
   */
  protected function setAddresses(EmailInterface $email, array $addresses) {
    $email->setReplyTo(...$addresses);
  }

}
