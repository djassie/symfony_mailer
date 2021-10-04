<?php

namespace Drupal\symfony_mailer;

use Drupal\symfony_mailer\Email;

interface MailBuilderInterface {

  /**
   * Builds an email message.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to build.
   */
  public function build(Email $email);

}
