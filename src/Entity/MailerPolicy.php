<?php

namespace Drupal\symfony_mailer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Plugin\DefaultLazyPluginCollection;

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

  /**
   * The unique ID of the policy record.
   *
   * @var string
   */
  protected $id = NULL;

  /**
   * Email builder configuration for this policy record.
   *
   * An associative array of builder configuration, keyed by the plug-in ID
   * with value as an array of configured settings.
   */
  protected $configuration = [];

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

}
