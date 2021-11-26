<?php

namespace Drupal\symfony_mailer;

/**
 * Defines the interface for an Email that has already been rendered.
 *
 * Any module can use this interface to adjust the email according to site
 * policy. Contains an inner Symfony email object that will be sent; use this
 * to configure email headers and other settings.
 */
interface RenderedEmailInterface extends BaseEmailInterface {

  /**
   * Gets the inner Symfony email that will be sent.
   *
   * @return \Symfony\Component\Mime\Email
   *   Inner Symfony email.
   */
  public function getInner();

  /**
   * Sets the HTML body.
   *
   * @param ?string $body
   *   HTML body.
   *
   * @return $this
   */
  public function setHtmlBody(?string $body);

  /**
   * Gets the HTML body.
   *
   * @return ?string
   *   HTML body.
   */
  public function getHtmlBody();

  /**
   * Sets the mail transport DSN to use.
   *
   * @param string $dsn
   *   Symfony mail transport DSN.
   *
   * @return $this
   */
  public function setTransportDsn(string $dsn);

  /**
   * Gets the mail transport DSN that will be used.
   *
   * @return string
   *   Transport DSN.
   */
  public function getTransportDSN();

}
