<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  protected string $type;
  protected string $entity_id;

  /**
   * The email subject.
   *
   * @var \Drupal\Component\Render\MarkupInterface|string
   */
  protected $subject;

  protected $body = [];
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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\BaseEmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   * @param ?\Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   */
  public function __construct(MailerInterface $mailer, EmailBuilderManager $email_builder_manager, RendererInterface $renderer, EntityTypeManagerInterface $entity_type_manager, string $type, string $sub_type, ?ConfigEntityInterface $entity) {
    $this->mailer = $mailer;
    $this->emailBuilderManager = $email_builder_manager;
    $this->renderer = $renderer;
    $this->entityTypeManager = $entity_type_manager;
    $this->type = $type;
    $this->subType = $sub_type;
    $this->entity = $entity;
  }

  /**
   * Creates an email object.
   *
   * Use EmailFactory instead of calling this directly.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\BaseEmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   * @param ?\Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   *
   * @return static
   *   A new email object.
   */
  public static function create(ContainerInterface $container, string $type, string $sub_type, ?ConfigEntityInterface $entity = NULL) {
    return new static(
      $container->get('symfony_mailer'),
      $container->get('plugin.manager.email_builder'),
      $container->get('renderer'),
      $container->get('entity_type.manager'),
      $type,
      $sub_type,
      $entity
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setSubject($subject) {
    // We must not force conversion of the subject to a string as this could
    // cause translation before switching to the correct language.
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
  public function setBody($body) {
    $this->body = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function appendBody($body) {
    $name = 'n' . count($this->body);
    $this->body[$name] = $body;
    return $this;
  }

  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   */
  public function appendBodyEntity(EntityInterface $entity, $view_mode = 'full') {
    $build = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())
      ->view($entity, $view_mode);

    $this->appendBody($build);
    return $this;
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
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubType() {
    return $this->subType;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getSuggestions(string $initial, string $join) {
    $part_array = [$this->type, $this->subType];
    if (isset($this->entity)) {
      $part_array[] = $this->entity->id();
    }

    $part = $initial ?: array_shift($part_array);
    $suggestions[] = $part;

    while ($part_array) {
      $part .= $join . array_shift($part_array);
      $suggestions[] = $part;
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
  public function setParam(string $key, $value) {
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
  public function setVariables(array $variables) {
    $this->variables = $variables;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariable(string $key, $value) {
    $this->variables[$key] = $value;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariables() {
    return $this->variables;
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
