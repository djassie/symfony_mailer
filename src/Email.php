<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email extends SymfonyEmail {

  protected $key;
  protected $content = [];
  protected $isHtml = TRUE;
  protected $libraries = [];

  /**
   * The mail transport.
   *
   * @var Symfony\Component\Mailer\Transport\TransportInterface
   */
  protected $transport;

  protected $data = [];
  protected $tokenReplace = FALSE;
  protected $tokenData;
  protected $tokenOptions;
  protected $sending = FALSE;

  /**
   * Constructs the Email object.
   *
   * @param string $key
   *   Message key, in the form "MODULE.TYPE".
   */
  public function __construct($key) {
    parent::__construct();
    $this->key = $key;
  }

  /**
   * Gets the message key.
   *
   * @return string
   *   Message key, in the form "MODULE.TYPE".
   */
  public function getKey() {
    return $this->key;
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

  public function getContent() {
    return $this->content;
  }

  /**
   * Sets whether to send the email as HTML.
   *
   * @param bool $is_html
   *   TRUE to send as HTML content type, FALSE to send as plain text.
   *
   * @return $this
   */
  public function enableHtml($is_html) {
    $this->isHtml = $is_html;
    return $this;
  }

  /**
   * Queries whether to send the email as HTML.
   *
   * @return bool
   *   TRUE to send as HTML content type, FALSE to send as plain text.
   */
  public function isHtml() {
    return $this->isHtml;
  }

  /**
   * Adds an asset library to use as mail CSS.
   *
   * @param string $library
   *   Library name, in the form "THEME/LIBRARY".
   *
   * @return $this
   */
  public function addLibrary($library) {
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
   * Sets the mail transport to use.
   *
   * @param Symfony\Component\Mailer\Transport\TransportInterface $transport
   *   Transport interface.
   *
   * @return $this
   */
  public function setTransport(TransportInterface $transport) {
    $this->transport = $transport;
    return $this;
  }

  /**
   * Gets the mail transport to use.
   *
   * @return Symfony\Component\Mailer\Transport\TransportInterface
   *   Transport interface.
   */
  public function getTransport() {
    return $this->transport;
  }

  /**
   * Sets data to pass to the email template and use in token replacement.
   *
   * @param array $data
   *   (optional) An array of keyed objects.
   *
   * @return $this
   */
  public function data(array $data = []) {
    $this->data = $data;
    return $this;
  }

  /**
   * Gets data to pass to the email template and use in token replacement.
   *
   * @return array
   *   An array of keyed objects.
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Enables tokens replacement in the message subject and body.
   *
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process.
   *
   * @return $this
   */
  public function enableTokenReplace(array $options = []) {
    $this->tokenReplace = TRUE;
    $this->tokenOptions = $options;
    return $this;
  }

  public function requiresTokenReplace() {
    return $this->tokenReplace;
  }

  public function getTokenOptions() {
    return $this->tokenOptions;
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

  /**
   * {@inheritdoc}
   */
  public function text($body, string $charset = 'utf-8') {
    if (!$this->sending) {
      throw new \exception('Use the content() method to set the message body.');
    }
    return parent::text($body, $charset);
  }

  /**
   * {@inheritdoc}
   */
  public function html($body, string $charset = 'utf-8') {
    if (!$this->sending) {
      throw new \exception('Use the content() method to set the message body.');
    }
    return parent::html($body, $charset);
  }

  /**
   * Marks that the email is sending.
   *
   * @internal
   */
  public function sending() {
    $this->sending = TRUE;
  }

}
