<?php

namespace Drupal\symfony_mailer;

/**
 * Interface for mailer service.
 */
interface MailerInterface
{
  /**
   * Sends an email.
   *
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email to send.
   *
   * @return bool
   *   Whether successful.
   */
  public function send(UnrenderedEmailInterface $email);

}
