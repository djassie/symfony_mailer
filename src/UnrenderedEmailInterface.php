<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Render\RendererInterface;

interface UnrenderedEmailInterface extends BaseEmailInterface {

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
   * Sets the email body.
   *
   * @param array $body
   *   Render array for the email body. This will be rendered using a template
   *   that can add header or footer markup.
   *
   * @return $this
   */
  public function setBody(array $body);

  /**
   * Appends content to the email body.
   *
   * @param array $body
   *   Array to append to the body render array.
   *
   * @return $this
   */
  public function appendBody(array $body);

  /**
   * Appends a string to the email body.
   *
   * @param string $text
   *   String to append to the body render array in a paragraph tag.
   *
   * @return $this
   */
  public function appendBodyParagraph(string $text);

  /**
   * Gets the un-rendered email body.
   *
   * @return array
   *   Body render array.
   */
  public function getBody();

  /**
   * Sets one or more to addresses.
   *
   * @param Address|string ...$addresses
   *
   * @return $this
   */
  public function setTo(...$addresses);

  /**
   * Gets the to addresses.
   *
   * @return array
   *   The to addresses.
   */
  public function getTo();

  /**
   * Sets one or more reply-to addresses.
   *
   * @param Address|string ...$addresses
   *
   * @return $this
   */
  public function setReplyTo(...$addresses);

  /**
   * Gets the reply-to addresses.
   *
   * @return array
   *   The reply-to addresses.
   */
  public function getReplyTo();

  /**
   * Gets alter callbacks.
   *
   * @param string $type
   *   The callback type: pre or post.
   *
   * @return array
   *   Array of callbacks.
   */
  public function getAlter(string $type);

  /**
   * Add an alter callback.
   *
   * @param string $type
   *   The callback type: pre or post.
   * @param callable $callable
   *   The function to call.
   */
  public function addAlter(string $type, callable $callable);

  /**
   * Gets the message key.
   *
   * @return array
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   */
  public function getKey();

  /**
   * Gets an array of 'suggestions' for the message key.
   *
   * @param string $initial
   *   The initial suggestion.
   * @param string $join
   *   The 'glue' to join each part of the key array with.
   *
   * @return array
   *   Suggestions, formed by taking the initial part and incrementally adding
   *   each part of the key.
   */
  public function getKeySuggestions(string $initial, string $join);

  /**
   * Sets the langcode.
   *
   * @param string $langcode
   *   Language code to use to compose the email.
   *
   * @return $this
   */
  public function setLangcode(string $langcode);

  /**
   * Sets parameters for hooks and to pass to the email template.
   *
   * @param array $params
   *   (optional) An array of keyed objects.
   *
   * @return $this
   */
  public function setParams(array $params = []);

  /**
   * Adds a parameter for hooks and to pass to the email template.
   *
   * @param string $key
   *   Parameter key to set.
   * @param $value
   *   Parameter value to set.
   *
   * @return $this
   */
  public function addParam(string $key, $value);

  /**
   * Sends the email.
   */
  public function send();

  /**
   * Renders the email.
   *
   * @internal
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   *
   * @return RenderedEmailInterface
   *   Rendered email.
   */
  public function render(RendererInterface $renderer);

}
