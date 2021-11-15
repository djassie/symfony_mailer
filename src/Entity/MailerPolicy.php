<?php

namespace Drupal\symfony_mailer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
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
  protected $id;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\EmailBuilderManager
   */
  protected $emailBuilderManager;

  protected $type;
  protected $subType;
  protected $entityId;
  protected $entityLabel;
  protected $builderDefinition;

  /**
   * Email builder configuration for this policy record.
   *
   * An associative array of builder configuration, keyed by the plug-in ID
   * with value as an array of configured settings.
   */
  protected $configuration = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
    $this->emailBuilderManager = \Drupal::service('plugin.manager.email_builder');
    $this->labelUnknown = $this->t('Unknown');
    $this->labelAll = $this->t('<b>*All*</b>');
    $this->labelInvalid = $this->t('<b>*Invalid*</b>');

    // The root policy with ID '_' applies to all types.
    if (!$this->id || ($this->id == '_')) {
      $this->builderDefinition = ['label' => $this->labelAll];
      return;
    }

    list($this->type, $this->subType, $this->entityId) = array_pad(explode('.', $this->id), 3, NULL);
    $this->builderDefinition = $this->emailBuilderManager->getDefinition($this->type, FALSE);

    if (!$this->builderDefinition) {
      $this->builderDefinition = ['label' => $this->labelUnknown];
    }
    elseif (!$this->builderDefinition['has_entity'] && $this->entityId) {
      $this->entityLabel = $this->labelInvalid;
    }
  }

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
    return $this->builderDefinition['label'];
  }

  /**
   * Gets a human-readable label for the the email sub-type.
   *
   * @return ?string
   *   Email sub-type label, or NULL if the policy applies to all sub-types.
   */
  public function getSubTypeLabel() {
    if ($this->subType) {
      return $this->builderDefinition['sub_types'][$this->subType] ?? $this->labelUnknown;
    }
    return $this->labelAll;
  }

  /**
   * Gets a human-readable label for the config entity this policy applies to.
   *
   * @return string
   *   Email config entity label, or NULL if the policy applies to all
   *   entities.
   */
  public function getEntityLabel() {
    if ($this->entityId) {
      if (!$this->entityLabel) {
        $entity = $this->entityTypeManager()->getStorage($this->type)->load($this->entityId);
        $this->entityLabel = $entity ? $entity->label() : $this->labelUnknown;
      }
      return $this->entityLabel;
    }
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
   * Gets a short human-readable summary of the configured policy.
   *
   * @return string
   *   Summary text.
   */
  public function getSummary() {
    $summary = [];
    foreach (array_keys($this->getConfiguration()) as $plugin_id) {
      if ($definition = $this->emailBuilderManager->getDefinition($plugin_id, FALSE)) {
        $summary[] = $definition['label'];
      }
    }
    return implode(', ', $summary);
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    parent::calculateDependencies();
    if ($provider = $this->builderDefinition['provider'] ?? NULL) {
      // @todo If $entityId then instead depend on that specific entity.
      $this->addDependency('module', $provider);
    }
    return $this;
  }

  /**
   * Helper callback to sort entities.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    return strnatcasecmp($a->getTypeLabel(), $b->getTypeLabel()) ?:
      strnatcasecmp($a->getSubTypeLabel(), $b->getSubTypeLabel()) ?:
      strnatcasecmp($a->getEntityLabel(), $b->getEntityLabel());
  }

}
