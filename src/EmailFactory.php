<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterManager;
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
   * @var \Drupal\symfony_mailer\Processor\EmailAdjusterManager
   */
  protected $emailAdjusterManager;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs the EmailFactory object.
   *
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface $email_builder_manager
   *   The email builder manager.
   * @param \Drupal\symfony_mailer\Processor\EmailAdjusterManager $email_adjuster_manager
   *   The email adjuster manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(EmailBuilderManagerInterface $email_builder_manager, EmailAdjusterManager $email_adjuster_manager, ModuleHandlerInterface $module_handler) {
    $this->emailBuilderManager = $email_builder_manager;
    $this->emailAdjusterManager = $email_adjuster_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function sendModuleEmail(string $module, string $sub_type, ...$params) {
    return $this->newModuleEmail($module, $sub_type, ...$params)->send();
  }

  /**
   * {@inheritdoc}
   */
  public function sendEntityEmail(ConfigEntityInterface $entity, string $sub_type, ...$params) {
    return $this->newEntityEmail($entity, $sub_type, ...$params)->send();
  }

  /**
   * {@inheritdoc}
   */
  public function newModuleEmail(string $module, string $sub_type, ...$params) {
    $email = Email::create(\Drupal::getContainer(), $module, $sub_type);
    return $this->initEmail($email, ...$params);
  }

  /**
   * {@inheritdoc}
   */
  public function newEntityEmail(ConfigEntityInterface $entity, string $sub_type, ...$params) {
    $email = Email::create(\Drupal::getContainer(), $entity->getEntityTypeId(), $sub_type, $entity);
    return $this->initEmail($email, ...$params);
  }

  /**
   * Initializes an email.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to initialize.
   *
   * @return \Drupal\symfony_mailer\EmailInterface
   *   The email.
   */
  protected function initEmail(EmailInterface $email, ...$params) {
    // Load builders with matching ID.
    foreach ($email->getSuggestions('', '.') as $plugin_id) {
      if ($this->emailBuilderManager->hasDefinition($plugin_id)) {
        $builder = $this->emailBuilderManager->createInstance($plugin_id);
        if (empty($created)) {
          $builder->createParams($email, ...$params);
          $created = TRUE;
        }
        $builder->init($email);
      }
    }

    // Apply policy.
    $this->emailAdjusterManager->applyPolicy($email);

    // Call hooks.
    foreach ($email->getSuggestions('', '__') as $hook_variant) {
      $this->moduleHandler->invokeAll("mailer_{$hook_variant}_init", [$email]);
    }
    $email->initDone();

    return $email;
  }

}
