<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email implements UnrenderedEmailInterface, RenderedEmailInterface {

  /**
   * The mailer.
   *
   * @var Drupal\symfony_mailer\MailerInterface $mailer
   */
  protected $mailer;

  protected array $key;
  protected $subject;
  protected array $body = [];
  protected array $to = [];
  protected array $replyTo = [];
  protected $alter = ['pre' => [], 'post' => []];
  protected $langcode;
  protected $params = [];

  protected SymfonyEmail $inner;

  protected $libraries = [];

  /**
   * The mail transport ID.
   *
   * @var string
   */
  protected string $transport = '';

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
    $this->mailer = $mailer;
    $this->key = $key;
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject($subject) {
    $this->subject = $subject;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubject() {
    return $this->subject;
  }

  /**
   * {@inheritdoc}
   */
  public function setBody(array $body) {
    $this->body = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function appendBody(array $body) {
    $name = 'n' . count($this->body);
    $this->body[$name] = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function appendBodyParagraph(string $text) {
    $element = [
      '#markup' => $text,
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    return $this->appendBody($element);
  }

  /**
   * {@inheritdoc}
   */
  public function getBody() {
    return $this->body;
  }

  /**
   * {@inheritdoc}
   */
  public function setTo(...$addresses) {
    $this->to = $addresses;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTo() {
    return $this->to;
  }

  /**
   * {@inheritdoc}
   */
  public function setReplyTo(...$addresses) {
    $this->replyTo = $addresses;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getReplyTo() {
    return $this->replyTo;
  }

  /**
   * {@inheritdoc}
   */
  public function getAlter(string $type) {
    return $this->alter[$type];
  }

  /**
   * {@inheritdoc}
   */
  public function addAlter(string $type, callable $callable) {
    $this->alter[$type][] = $callable;
  }

  /**
   * {@inheritdoc}
   */
  public function getKey() {
    return $this->key;
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function setLangcode(string $langcode) {
    $this->langcode = $langcode;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode() {
    return $this->langcode;
  }

  /**
   * {@inheritdoc}
   */
  public function setParams(array $params = []) {
    $this->params = $params;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function addParam(string $key, $value) {
    $this->params[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getParams() {
    return $this->params;
  }

  /**
   * {@inheritdoc}
   */
  public function getParam(string $key) {
    return $this->params[$key];
  }

  /**
   * {@inheritdoc}
   */
  public function send() {
    $this->mailer->send($this);
  }

  /**
   * {@inheritdoc}
   */
  public function render(RendererInterface $renderer) {
    // Render subject.
    $subject = ($this->subject instanceof MarkupInterface) ? PlainTextOutput::renderFromHtml($this->subject) : $this->subject;

    // Render body.
    $body = [
      '#theme' => 'email',
      '#email' => $this,
    ];

    $this->inner = (new SymfonyEmail())
      ->subject($subject)
      ->html((string) $renderer->renderPlain($body))
      ->to(...$this->to)
      ->replyTo(...$this->replyTo);
    $this->subject = NULL;
    $this->body = [];
    $this->to = [];
    $this->replyTo = [];

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getInner() {
    return $this->inner;
  }

  /**
   * {@inheritdoc}
   */
  public function setHtmlBody($body) {
    $this->inner->html($body);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getHtmlBody() {
    return $this->inner->getHtmlBody();
  }

  /**
   * {@inheritdoc}
   */
  public function addLibrary(string $library) {
    $this->libraries[] = $library;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * {@inheritdoc}
   */
  public function setTransport(string $transport) {
    $this->transport = $transport;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransport() {
    return $this->transport;
  }

}
