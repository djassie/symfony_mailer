<?php

namespace Drupal\symfony_mailer;

interface MailBuilderInterface {

  /**
   * Builds an email message.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to build.
   * @param string $key
   *   The second entry in the email key, which identifies the TYPE.
   * @param string $to
   *   The email address or addresses where the message will be sent to. The
   *   formatting of this string will be validated with the
   *   @link http://php.net/manual/filter.filters.validate.php PHP email validation filter. @endlink
   *   Some examples are:
   *   - user@example.com
   *   - user@example.com, anotheruser@example.com
   *   - User <user@example.com>
   *   - User <user@example.com>, Another User <anotheruser@example.com>
   * @param string $langcode
   *   Language code to use to compose the email.
   * @param array $params
   *   (optional) Parameters to build the email.
   */
  public function mail($email, $key, $to, $langcode, $params);

}
