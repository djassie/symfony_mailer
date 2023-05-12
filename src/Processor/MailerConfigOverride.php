<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Symfony Mailer configuration override.
 */
class MailerConfigOverride implements ConfigFactoryOverrideInterface {

  /**
   * Whether cache has been built.
   *
   * @var bool
   */
  protected $builtCache = FALSE;

  /**
   * Array of config overrides.
   *
   * As required by ConfigFactoryOverrideInterface::loadOverrides().
   *
   * @var array
   */
  protected $configOverrides = [];

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {
    if (!array_diff($names, ["core.extension", "symfony_mailer.settings"])) {
      // Avoid an infinite loop.
      return [];
    }

    $this->buildCache();
    return array_intersect_key($this->configOverrides, array_flip($names));
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'MailerConfigOverride';
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

  /**
   * Build cache of config overrides.
   */
  protected function buildCache() {
    if (!$this->builtCache) {
      // Getting the definitions can cause reading of config which triggers
      // `loadOverrides()` to call this function. Protect against an infinite
      // loop by marking the cache as built before starting.
      $this->builtCache = TRUE;

      // We cannot use dependency injection because that creates a circular
      // dependency.
      $builder_manager = \Drupal::service('plugin.manager.email_builder');

      foreach ($builder_manager->getDefinitions() as $definition) {
        $this->configOverrides = array_merge($this->configOverrides, $definition['config_overrides']);
      }
    }
  }

}
