<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for the Email Factory.
 */
interface EmailFactoryInterface {

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
  public function newModuleEmail(string $module, string $sub_type);

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
  public function newEntityEmail(ConfigEntityInterface $entity, string $sub_type);

}
