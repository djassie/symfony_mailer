<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterManagerInterface;
use Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface;

/**
 * Provides a factory for creating email objects.
 */
class EmailFactory implements EmailFactoryInterface {

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface
   */
  protected $emailBuilderManager;

  /**
   * The email adjuster manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailAdjusterManagerInterface
   */
  protected $emailAdjusterManager;

  /**
   * Constructs the EmailFactory object.
   *
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface $email_builder_manager
   *   The email builder manager.
   * @param \Drupal\symfony_mailer\Processor\EmailAdjusterManagerInterface $email_adjuster_manager
   *   The email adjuster manager.
   */
  public function __construct(EmailBuilderManagerInterface $email_builder_manager, EmailAdjusterManagerInterface $email_adjuster_manager) {
    $this->emailBuilderManager = $email_builder_manager;
    $this->emailAdjusterManager = $email_adjuster_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function sendTypedEmail(string $type, string $sub_type, ...$params) {
    $email = $this->newTypedEmail($type, $sub_type, ...$params);
    $email->send();
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function sendEntityEmail(ConfigEntityInterface $entity, string $sub_type, ...$params) {
    $email = $this->newEntityEmail($entity, $sub_type, ...$params);
    $email->send();
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function newTypedEmail(string $type, string $sub_type, ...$params) {
    $email = Email::create(\Drupal::getContainer(), $type, $sub_type);
    return $this->initEmail($email, $params);
  }

  /**
   * {@inheritdoc}
   */
  public function newEntityEmail(ConfigEntityInterface $entity, string $sub_type, ...$params) {
    $email = Email::create(\Drupal::getContainer(), $entity->getEntityTypeId(), $sub_type, $entity);
    return $this->initEmail($email, $params);
  }

  /**
   * Initializes an email.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to initialize.
   * @param array $params
   *   Parameters for building this email.
   *
   * @return \Drupal\symfony_mailer\EmailInterface
   *   The email.
   */
  protected function initEmail(EmailInterface $email, array $params) {
    // Process.
    $this->emailBuilderManager->applyBuilders($email, $params);
    $this->emailAdjusterManager->applyPolicy($email);
    $email->initDone();
    return $email;
  }

}
