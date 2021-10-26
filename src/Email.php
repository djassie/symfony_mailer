<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email implements UnrenderedEmailInterface, RenderedEmailInterface {

  /**
   * The mailer.
   *
   * @var \Drupal\symfony_mailer\MailerInterface $mailer
   */
  protected $mailer;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\EmailBuilderManager
   */
  protected $emailBuilderManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  protected array $key;
  protected $subject;
  protected array $body = [];
  protected array $to = [];
  protected array $replyTo = [];
  protected $builders = [];
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
   * @param \Drupal\symfony_mailer\MailerInterface $mailer
   *   Mailer service.
   * @param \Drupal\symfony_mailer\EmailBuilderManager
   *   The email builder manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param array $key
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   */
  public function __construct(MailerInterface $mailer, EmailBuilderManager $email_builder_manager, RendererInterface $renderer, array $key) {
    $this->mailer = $mailer;
    $this->emailBuilderManager = $email_builder_manager;
    $this->renderer = $renderer;
    $this->key = $key;
  }

  /**
   * Creates an email object.
   *
   * Use EmailFactory::newEmail() instead of calling this directly.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   * @param array $key
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   *
   * @return static
   *   A new email object.
   */
  public static function create(ContainerInterface $container, array $key) {
    return new static(
      $container->get('symfony_mailer'),
      $container->get('plugin.manager.email_builder'),
      $container->get('renderer'),
      $key
    );
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
  public function addBuilder(string $plugin_id, array $configuration = [], $optional = FALSE) {
    if (!$optional || $this->emailBuilderManager->hasDefinition($plugin_id)) {
      $this->builders[$plugin_id] = $this->emailBuilderManager->createInstance($plugin_id, $configuration);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getBuilders() {
    $this->emailBuilderManager->sort($this->builders);
    return $this->builders;
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
  public function render() {
    // Render subject.
    $subject = ($this->subject instanceof MarkupInterface) ? PlainTextOutput::renderFromHtml($this->subject) : $this->subject;

    // Render body.
    $body = [
      '#theme' => 'email',
      '#email' => $this,
    ];

    $this->inner = (new SymfonyEmail())
      ->subject($subject)
      ->html((string) $this->renderer->renderPlain($body))
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
