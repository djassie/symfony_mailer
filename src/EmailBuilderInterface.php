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

  /**
   * Gets the weight of the email builder.
   *
   * @return int
   *   The weight.
   */
  public function getWeight();

}
