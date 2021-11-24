<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;

/**
 * Provides the email adjuster plugin manager.
 */
class EmailAdjusterManager extends DefaultPluginManager {

  /**
   * Constructs the EmailBuilderManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EmailAdjuster', $namespaces, $module_handler, 'Drupal\symfony_mailer\EmailAdjusterInterface', 'Drupal\symfony_mailer\Annotation\EmailAdjuster');
    $this->setCacheBackend($cache_backend, 'symfony_mailer_adjuster_plugins');
    $this->alterInfo('mailer_adjuster_info');
  }

  /**
   * Applies email policy to an email message.
   *
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email.
   */
  public function applyPolicy(UnrenderedEmailInterface $email) {
    // Load policies, including the root policy that applies to all messages.
    $suggestions = array_merge(['_'], $email->getSuggestions('', '.'));
    foreach ($suggestions as $id) {
      if ($policy = MailerPolicy::load($id)) {
        $policy_config[] = $policy->getConfiguration();
      }
    }

    // Add adjusters.
    if (isset($policy_config)) {
      $policy_config = array_merge(...$policy_config);
      foreach ($policy_config as $plugin_id => $config) {
        if ($this->hasDefinition($plugin_id)) {
          $email->addProcessor($this->createInstance($plugin_id, $config));
        }
      }
    }
  }

}
