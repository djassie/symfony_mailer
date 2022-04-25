<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Session\AccountInterface;

/**
 * Defines an extended Email interface that adds internal functions.
 *
 * @internal
 */
interface InternalEmailInterface extends EmailInterface {

  /**
   * Runs processing of a phase for all email processors.
   *
   * @param int $phase
   *   The phase, one of the PHASE_ constants.
   *
   * @return $this
   */
  public function process(int $phase);

  /**
   * Customizes the email.
   *
   * Valid: before rendering.
   *
   * @param string $langcode
   *   The language code.
   * @param \Drupal\Core\Session\AccountInterface $address
   *   The account.
   *
   * @return $this
   */
  public function customize(string $langcode, AccountInterface $account);

  /**
   * Renders the email.
   *
   * Valid: before rendering.
   *
   * @return $this
   */
  public function render();

  /**
   * Get the phase of processing.
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
   * @return \Symfony\Component\Mime\Email
   *   Inner Symfony email.
   */
  public function getSymfonyEmail();

}
