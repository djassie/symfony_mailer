<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Interface for mailer service.
 */
interface MailerInterface
{
  /**
   * Creates a new blank email.
   *
   * @param array $key
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   *
   * @return
   *   New email.
   */
  public function newEmail(array $key);

  /**
   * Sends an email.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to send.
   *
   * @throws TransportExceptionInterface
   */
  public function send(Email $email);

}
