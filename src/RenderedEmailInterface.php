<?php

namespace Drupal\symfony_mailer;

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
   * Adds an asset library to use as mail CSS.
   *
   * @param string $library
   *   Library name, in the form "THEME/LIBRARY".
   *
   * @return $this
   */
  public function addLibrary(string $library);

  /**
   * Gets the libraries to use as mail CSS.
   *
   * @return array
   *   Array of library names, in the form "THEME/LIBRARY".
   */
  public function getLibraries();

  /**
   * Sets the mail transport ID to use.
   *
   * @param string $transport
   *   Transport ID.
   *
   * @return $this
   */
  public function setTransport(string $transport);

  /**
   * Gets the mail transport ID to use.
   *
   * @return string
   *   Transport ID.
   */
  public function getTransport();

}
