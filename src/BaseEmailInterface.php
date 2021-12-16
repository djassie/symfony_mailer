<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\DataPart;

/**
 * Defines an interface related to the Symfony Email object.
 *
 * The functions are mostly identical, except that set accessors are explicitly
 * named, e.g. setSubject() instead of subject().
 */
interface BaseEmailInterface {

  /**
   * Sets the email subject.
   *
   * @param \Drupal\Component\Render\MarkupInterface|string $subject
   *   The email subject.
   *
   * @return $this
   */
  public function setSubject($subject);

  /**
   * Gets the email subject.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string $subject
   *   The email subject.
   */
  public function getSubject();

  /**
   * @return $this
   */
  public function setDate(\DateTimeInterface $dateTime);

  public function getDate(): ?\DateTimeImmutable;

  /**
   * @return $this
   */
  public function setReturnPath($address);

  public function getReturnPath(): ?Address;

  /**
   * @return $this
   */
  public function setSender($address);

  public function getSender(): ?Address;

  /**
   * @return $this
   */
  public function addFrom(...$addresses);

  /**
   * @return $this
   */
  public function setFrom(...$addresses);

  /**
   * @return Address[]
   */
  public function getFrom(): array;

  /**
   * Sets one or more reply-to addresses.
   *
   * @param ...$addresses
   *
   * @return $this
   */
  public function addReplyTo(...$addresses);

  /**
   * @return $this
   */
  public function setReplyTo(...$addresses);

  /**
   * Gets the reply-to addresses.
   *
   * @return Address[]
   *   The reply-to addresses.
   */
  public function getReplyTo(): array;

  /**
   * @return $this
   */
  public function addTo(...$addresses);

  /**
   * Sets one or more to addresses.
   *
   * @param ...$addresses
   *
   * @return $this
   */
  public function setTo(...$addresses);

  /**
   * Gets the to addresses.
   *
   * @return Address[]
   *   The to addresses.
   */
  public function getTo(): array;

  /**
   * @return $this
   */
  public function addCc(...$addresses);

  /**
   * @return $this
   */
  public function setCc(...$addresses);

  /**
   * @return Address[]
   */
  public function getCc(): array;

  /**
   * @return $this
   */
  public function addBcc(...$addresses);

  /**
   * @return $this
   */
  public function setBcc(...$addresses);

  /**
   * @return Address[]
   */
  public function getBcc(): array;

  /**
   * Sets the priority of this message.
   *
   * The value is an integer where 1 is the highest priority and 5 is the lowest.
   *
   * @return $this
   */
  public function setPriority(int $priority);

  /**
   * Get the priority of this message.
   *
   * The returned value is an integer where 1 is the highest priority and 5
   * is the lowest.
   */
  public function getPriority(): int;

  /**
   * @param string $body
   *
   * @return $this
   */
  public function setTextBody(string $body);

  /**
   * @return ?string
   */
  public function getTextBody();

  /**
   * Sets the HTML body.
   *
   * Valid: after rendering.
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
   * Valid: after rendering.
   *
   * @return ?string
   *   HTML body.
   */
  public function getHtmlBody();

  /**
   * @param string $body
   *
   * @return $this
   */
  // public function attach(string $body, string $name = null, string $contentType = null);

  /**
   * @return $this
   */
  // public function attachFromPath(string $path, string $name = null, string $contentType = null);

  /**
   * @param string $body
   *
   * @return $this
   */
  // public function embed(string $body, string $name = null, string $contentType = null);

  /**
   * @return $this
   */
  // public function embedFromPath(string $path, string $name = null, string $contentType = null);

  /**
   * @return $this
   */
  // public function attachPart(DataPart $part);

  /**
   * @return array|DataPart[]
   */
  // public function getAttachments(): array;

  public function getHeaders(): Headers;

  /**
   * @return $this
   */
  public function addTextHeader(string $name, string $value);

};
