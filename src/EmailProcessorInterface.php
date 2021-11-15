<?php

namespace Drupal\symfony_mailer;

interface EmailProcessorInterface {

  /**
   * Runs pre-render functions to build an email message.
   *
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email to pre-render.
   */
  public function preRender(UnrenderedEmailInterface $email);

  /**
   * Runs post-render functions to adjust an email message.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to post-render.
   */
  public function postRender(RenderedEmailInterface $email);

  /**
   * Gets the weight of the email builder.
   *
   * @param string $function
   *   The function that will be called: 'preRender' or 'postRender'.
   *
   * @return int
   *   The weight.
   */
  public function getWeight(string $function);

}
