<?php

namespace Drupal\symfony_mailer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a Mailer Policy configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "mailer_policy",
 *   label = @Translation("Mailer Policy"),
 *   handlers = {
 *     "list_builder" = "Drupal\symfony_mailer\MailerPolicyListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\symfony_mailer\Form\PolicyForm",
 *       "add" = "Drupal\symfony_mailer\Form\PolicyAddForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailer",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/mailer/policy/{mailer_policy}",
 *     "delete-form" = "/admin/config/system/mailer/policy/{mailer_policy}/delete",
 *     "collection" = "/admin/config/system/mailer/policy",
 *   },
 *   config_export = {
 *     "id",
 *     "configuration",
 *   }
 * )
 */
class MailerPolicy extends ConfigEntityBase {
  use StringTranslationTrait;

  /**
   * The unique ID of the policy record.
   *
   * @var string
   */
  protected $id = NULL;

  protected $type;
  protected $subType;
  protected $entityId;
  protected $entityType;
  protected $builderDefinition;

  /**
   * Email builder configuration for this policy record.
   *
   * An associative array of builder configuration, keyed by the plug-in ID
   * with value as an array of configured settings.
   */
  protected $configuration = [];

  /**
   * Gets the email type this policy applies to.
   *
   * @return ?string
   *   Email type, or NULL if the policy applies to all types.
   */
  public function getType() {
    return $this->type;
  }

  /**
   * Gets the email sub-type this policy applies to.
   *
   * @return ?string
   *   Email sub-type, or NULL if the policy applies to all sub-types.
   */
  public function getSubType() {
    return $this->subType;
  }

  /**
   * Gets a human-readable label for the email type this policy applies to.
   *
   * @return string
   *   Email type label.
   */
  public function getTypeLabel() {
    if (!$this->type) return $this->t('All');
    return $this->entityType ? $this->entityType->getLabel() : \Drupal::moduleHandler()->getName($this->type);
  }

  /**
   * Gets a human-readable label for the config entity this policy applies to.
   *
   * @return string
   *   Email config entity label, or NULL if the policy applies to all
   *   entities.
   */
  public function getEntityLabel() {
    if (!$this->entityId) {
      return NULL;
    }
    if (!$this->entity) {
      $this->entity = $this->entityTypeManager()->getStorage($this->entityType)->load($this->entityId);
    }
    return $this->entity->label();
  }

  /**
   * Gets the email builder configuration for this policy record.
   *
   * @return array
   *   An associative array of builder configuration, keyed by the plug-in ID
   *   with value as an array of configured settings.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    // The root policy with ID '_' has no type.
    if ($this->id == '_') {
      return;
    }

    list($this->type, $this->subType, $this->entityId) = array_pad(explode('.', $this->id), 3, NULL);
    $this->emailBuilderManager = \Drupal::service('plugin.manager.email_builder');
    $this->builderDefinition = $this->emailBuilderManager->getDefinition("type.$this->type");

    if ($this->builderDefinition['has_entity']) {
      $this->entityType = $this->entityTypeManager()->getDefinition($this->type);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    if ($this->type) {
      $module = $this->entityType ? $this->entityType->getProvider() : $this->type;
      $this->addDependency('module', $module);
    }
    return $this;
  }

}
