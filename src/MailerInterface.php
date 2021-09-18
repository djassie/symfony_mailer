<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Interface for mailer service.
 */
interface MailerInterface
{
  /**
   * Sends an email.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to send.
   *
   * @return bool
   *   Whether successful.
   */
  public function send(Email $email);

}
