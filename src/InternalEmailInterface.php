<?php

namespace Drupal\symfony_mailer;

/**
 * Defines an extended Email interface that adds internal functions.
 */
interface InternalEmailInterface extends EmailInterface {

  /**
   * Runs processing of a phase for all email processors.
   *
   * @internal
   *
   * @param int $phase
   *   The phase, one of the PHASE_ constants.
   *
   * @return $this
   */
  public function process(int $phase);

  /**
   * Renders the email.
   *
   * Valid: before rendering.
   *
   * @internal
   *
   * @return $this
   */
  public function render();

  /**
   * Get the phase of processing.
   *
   * @internal
   *
   * @return int
   *   The phase, one of the PHASE_ constants.
   */
  public function getPhase();

  /**
   * Gets the inner Symfony email to send.
   *
   * Valid: after rendering.
   *
   * @internal
   *
   * @return \Symfony\Component\Mime\Email
   *   Inner Symfony email.
   */
  public function getSymfonyEmail();

}
