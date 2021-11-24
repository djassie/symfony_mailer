<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\Email as SymfonyEmail;

class Email implements UnrenderedEmailInterface, RenderedEmailInterface {

  /**
   * The mailer.
   */
  protected MailerInterface $mailer;

  /**
   * The renderer.
   */
  protected RendererInterface $renderer;

  /**
   * The entity type manager.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

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
  protected array $processors = [];
  protected $processorIterator = NULL;
  protected string $langcode;
  protected array $params = [];
  protected array $variables = [];

  protected SymfonyEmail $inner;

  protected array $libraries = [];

  /**
   * The mail transport.
   */
  protected TransportInterface $transport;

  /**
   * Constructs the Email object.
   *
   * @param \Drupal\symfony_mailer\MailerInterface $mailer
   *   Mailer service.
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
  public function __construct(MailerInterface $mailer, RendererInterface $renderer, EntityTypeManagerInterface $entity_type_manager, string $type, string $sub_type, ?ConfigEntityInterface $entity) {
    $this->mailer = $mailer;
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
  public function addProcessor(EmailProcessorInterface $processor) {
    $this->processors[] = $processor;
    if ($this->processorIterator) {
      $this->processorIterator->add($processor);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getProcessors() {
    // @todo We are no longer using the feature to add builders during
    // iteration so maybe we can remove EmailProcessorIterator.
    $function = isset($this->inner) ? 'postRender' : 'preRender';
    $this->processorIterator = new EmailProcessorIterator(array_values($this->processors), $function);
    return $this->processorIterator;
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
    $subject = $this->subject;
    if (is_array($subject)) {
      $subject = $this->renderer->renderPlain($subject);
    }
    if ($subject instanceof MarkupInterface) {
      $subject = PlainTextOutput::renderFromHtml($subject);
    }

    // Render body.
    $body = ['#theme' => 'email', '#email' => $this];
    $body = $this->renderer->renderPlain($body);

    $this->inner = (new SymfonyEmail())
      ->html($body)
      ->subject($subject)
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
  public function setTransport(TransportInterface $transport) {
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
