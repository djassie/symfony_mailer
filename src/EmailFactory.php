<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
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
   * Constructs the EmailFactory object.
   *
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface $email_builder_manager
   *   The email builder manager.
   */
  public function __construct(EmailBuilderManagerInterface $email_builder_manager) {
    $this->emailBuilderManager = $email_builder_manager;
  }

  /**
   * Creates an email object unrelated to a config entity.
   *
   * @param string $module
   *   The module name.
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\EmailInterface::getSubType()
   *
   * @return \Drupal\symfony_mailer\EmailInterface
   *   A new email object.
   */
  public function newModuleEmail(string $module, string $sub_type) {
    return $this->newEmail($module, $sub_type);
  }

  /**
   * Creates an email object from a config entity.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\EmailInterface::getEntity()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\EmailInterface::getSubType()
   *
   * @return \Drupal\symfony_mailer\EmailInterface
   *   A new email object.
   */
  public function newEntityEmail(ConfigEntityInterface $entity, string $sub_type) {
    return $this->newEmail($entity->getEntityTypeId(), $sub_type, $entity);
  }

  /**
   * Creates an email.
   *
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\EmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\EmailInterface::getSubType()
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   (optional) Entity. @see \Drupal\symfony_mailer\EmailInterface::getEntity()
   *
   * @return \Drupal\symfony_mailer\EmailInterface
   *   A new email object.
   */
  protected function newEmail(string $type, string $sub_type, ?ConfigEntityInterface $entity = NULL) {
    $email = Email::create(\Drupal::getContainer(), $type, $sub_type, $entity);

    // Load builders with matching ID.
    foreach ($email->getSuggestions('', '.') as $plugin_id) {
      if ($this->emailBuilderManager->hasDefinition($plugin_id)) {
        $email->addProcessor($this->emailBuilderManager->createInstance($plugin_id));
      }
    }

    return $email;
  }

}
