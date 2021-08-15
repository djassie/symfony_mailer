<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Interface for mailer service.
 */
interface MailerInterface
{
  /**
   * Sends a message.
   *
   * @param \Drupal\symfony_mailer\Email $message
   *   The message to send.
   *
   * @throws TransportExceptionInterface
   */
  public function send(Email $message);

}
