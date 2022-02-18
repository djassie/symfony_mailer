<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides the mailer helper service.
 */
interface MailerHelperInterface {

  /**
   * Renders an element that lists policy related to a config entity.
   *
   * The element is designed for insertion into a config edit page for an
   * entity that has a related EmailBuilder.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Config entity being edited.
   * @param string $subtype
   *   Sub-type of the policies to show.
   * @param string[] $common_adjusters
   *   ID of EmailAdjusters to use as an example in the description.
   *
   * @return array
   *   The render array.
   */
  public function renderEntityPolicy(ConfigEntityInterface $entity, string $subtype, array $common_adjusters = ['email_subject', 'email_from']);

  /**
   * Renders an element that lists policy for a specific type.
   *
   * The element is designed for insertion into a settings page for a module.
   *
   * @param string $type
   *   Type of the policies to show.
   * @param string[] $common_adjusters
   *   ID of EmailAdjusters to use as an example in the description.
   *
   * @return array
   *   The render array.
   */
  public function renderTypePolicy(string $type, array $common_adjusters = ['email_subject', 'email_from']);

}
