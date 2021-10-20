<?php

namespace Drupal\symfony_mailer;

interface EmailBuilderInterface {

  /**
   * Builds an email message.
   *
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email to build.
   */
  public function build(UnrenderedEmailInterface $email);

  /**
   * Adjusts an email message.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to adjust.
   */
  public function adjust(RenderedEmailInterface $email);

}
