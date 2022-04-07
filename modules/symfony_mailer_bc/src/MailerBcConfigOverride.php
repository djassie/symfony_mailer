<?php

namespace Drupal\symfony_mailer_bc;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Example configuration override.
 */
class MailerBcConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    $overrides = [];
    if (in_array('user.setting', $names)) {
      $overrides['user.setting']['notify'] = [
        'cancel_confirm' => TRUE,
        'password_reset' => TRUE,
        'status_activated' => TRUE,
        'status_blocked' => TRUE,
        'status_canceled' => TRUE,
        'register_admin_created' => TRUE,
        'register_no_approval_required' => TRUE,
        'register_pending_approval' => TRUE,
      ];
    }
    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'MailerBcConfigOverride';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
