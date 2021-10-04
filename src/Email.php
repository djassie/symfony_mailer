<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email extends SymfonyEmail {

  /**
   * The mailer.
   *
   * @var Drupal\symfony_mailer\MailerInterface $mailer
   */
  protected $mailer;

  protected $alter = ['pre' => [], 'post' => []];

  protected array $key;
  protected array $content = [];
  protected $libraries = [];

  /**
   * The mail transport.
   *
   * @var Symfony\Component\Mailer\Transport\TransportInterface
   */
  protected $transport;

  protected $langcode;
  protected $params = [];
  protected $sending = FALSE;

  /**
   * Constructs the Email object.
   *
   * Use MailerFactory::newEmail() instead of calling this directly.
   *
   * @param Drupal\symfony_mailer\MailerInterface $mailer
   *   Mailer service.
   * @param array $key
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   */
  public function __construct(MailerInterface $mailer, array $key) {
    parent::__construct();
    $this->mailer = $mailer;
    $this->key = $key;
  }

  /**
   * Sends the email.
   */
  public function send() {
    $this->mailer->send($this);
  }

  /**
   * Gets alter callbacks.
   *
   * @param string $type
   *   The callback type: pre or post.
   *
   * @return array
   *   Array of callbacks.
   */
  public function getAlter(string $type) {
    return $this->alter[$type];
  }

  /**
   * Add an alter callback.
   *
   * @param string $type
   *   The callback type: pre or post.
   * @param callable $callable
   *   The function to call.
   */
  public function addAlter(string $type, callable $callable) {
    $this->alter[$type][] = $callable;
  }

  /**
   * Gets the message key.
   *
   * @return array
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   */
  public function getKey() {
    return $this->key;
  }

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
  public function getKeySuggestions(string $initial, string $join) {
    $key_array = $this->key;
    $key = $initial ?: array_shift($key_array);
    $suggestions[] = $key;

    while ($key_array) {
      $key .= $join . array_shift($key_array);
      $suggestions[] = $key;
    }

    return $suggestions;
  }

  /**
   * Sets the content to use for creating the HTML/plain email body.
   *
   * Use this function instead of calling text() or html() directly.
   *
   * @param array $content
   *   Render array for the email body content. This will be rendered using a
   *   template that can add header or footer markup.
   *
   * @return $this
   */
  public function content(array $content) {
    $this->content = $content;
    return $this;
  }

  /**
   * Appends content to use for creating the HTML/plain email body.
   *
   * @param array $content
   *   Array to append to the content render array.
   *
   * @return $this
   */
  public function appendContent(array $content) {
    $name = 'n' . count($this->content);
    $this->content[$name] = $content;
    return $this;
  }

  /**
   * Appends string content to use for creating the HTML/plain email body.
   *
   * @param string $text
   *   String to append to the content render array in a paragraph tag.
   *
   * @return $this
   */
  public function appendParagraph(string $text) {
    $element = [
      '#markup' => $text,
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    return $this->appendContent($element);
  }

  /**
   * Gets content to use for creating the HTML/plain email body.
   *
   * @return array
   *   Content render array.
   */
  public function getContent() {
    return $this->content;
  }

  /**
   * Adds an asset library to use as mail CSS.
   *
   * @param string $library
   *   Library name, in the form "THEME/LIBRARY".
   *
   * @return $this
   */
  public function addLibrary(string $library) {
    $this->libraries[] = $library;
    return $this;
  }

  /**
   * Gets the libraries to use as mail CSS.
   *
   * @return array
   *   Array of library names, in the form "THEME/LIBRARY".
   */
  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * Sets the mail transport ID to use.
   *
   * @param string $transport
   *   Transport ID.
   *
   * @return $this
   */
  public function transport(string $transport) {
    $this->transport = $transport;
    return $this;
  }

  /**
   * Gets the mail transport ID to use.
   *
   * @return string
   *   Transport ID.
   */
  public function getTransport() {
    return $this->transport;
  }

  /**
   * Sets the langcode.
   *
   * @param string $langcode
   *   Language code to use to compose the email.
   *
   * @return $this
   */
  public function langcode(string $langcode) {
    $this->langcode = $langcode;
    return $this;
  }

  /**
   * Gets the langcode.
   *
   * @return string
   *   Language code to use to compose the email.
   */
  public function getLangcode() {
    return $this->langcode;
  }

  /**
   * Sets parameters for hooks and to pass to the email template.
   *
   * @param array $params
   *   (optional) An array of keyed objects.
   *
   * @return $this
   */
  public function params(array $params = []) {
    $this->params = $params;
    return $this;
  }

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
  public function addParam(string $key, $value) {
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * Gets parameters to pass to the email template and for token replacement.
   *
   * @return array
   *   An array of keyed objects.
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * Gets a parameter to pass to the email template and for token replacement.
   *
   * @param string $key
   *   Parameter key to get.
   *
   * @return mixed
   *   Parameter value.
   */
  public function getParam(string $key) {
    return $this->params[$key];
  }

  /**
   * {@inheritdoc}
   */
  public function subject($subject) {
    // @todo Is this safe in the global render context? Could instead save the
    // unaltered subject and render it in sending().
    if ($subject instanceof MarkupInterface) {
      $subject = PlainTextOutput::renderFromHtml($subject);
    }
    return parent::subject($subject);
  }

}
