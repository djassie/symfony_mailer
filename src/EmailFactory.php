<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;

/**
 * Provides a factory for create email objects.
 */
class EmailFactory {

  /**
   * Creates an email object unrelated to a config entity.
   *
   * @param string $module
   *   The module name.
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   *
   * @return \Drupal\symfony_mailer\UnrenderedEmailInterface
   *   A new email object.
   */
  public function newModuleEmail(string $module, string $sub_type) {
    return $this->newEmail($module, $sub_type);
  }

  /**
   * Creates an email object from a config entity.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   *
   * @return \Drupal\symfony_mailer\UnrenderedEmailInterface
   *   A new email object.
   */
  public function newEntityEmail(ConfigEntityInterface $entity, string $sub_type) {
    return $this->newEmail($entity->getEntityTypeId(), $sub_type, $entity);
  }

  /**
   * Creates an email.
   *
   * @param string $type
   *   Type. @see \Drupal\symfony_mailer\BaseEmailInterface::getType()
   * @param string $sub_type
   *   Sub-type. @see \Drupal\symfony_mailer\BaseEmailInterface::getSubType()
   * @param ?\Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Entity. @see \Drupal\symfony_mailer\BaseEmailInterface::getEntity()
   *
   * @return \Drupal\symfony_mailer\UnrenderedEmailInterface
   *   A new email object.
   */
  protected function newEmail(string $type, string $sub_type, ?ConfigEntityInterface $entity = NULL) {
    $email = Email::create(\Drupal::getContainer(), $type, $sub_type, $entity);

    // Load builders and policy with matching ID.
    foreach ($email->getSuggestions('', '.') as $id) {
      $email->addBuilder($id, [], TRUE);
      if ($policy = MailerPolicy::load($id)) {
        $policy_config[] = $policy->getConfiguration();
      }
    }

    if (isset($policy_config)) {
      $policy_config = array_merge(...$policy_config);
      foreach ($policy_config as $plugin_id => $config) {
        $email->addBuilder($plugin_id, $config, TRUE);
      }
    }

    // @todo Could move this into the policy config so it's visible and
    // customisable from the GUI.
    $email->addBuilder('default_headers')
      ->addBuilder('url_to_absolute')
      ->addBuilder('html_to_text')
      ->addBuilder('inline_css');

    return $email;
  }

}
