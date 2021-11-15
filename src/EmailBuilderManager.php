<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Plugin\FilteredPluginManagerInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Provides the email builder plugin manager.
 */
class EmailBuilderManager extends DefaultPluginManager implements FilteredPluginManagerInterface {

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct('Plugin/EmailBuilder', $namespaces, $module_handler, 'Drupal\symfony_mailer\EmailBuilderInterface', 'Drupal\symfony_mailer\Annotation\EmailBuilder');
    $this->entityTypeManager = $entity_type_manager;
    $this->setCacheBackend($cache_backend, 'symfony_mailer_builder_plugins');
    $this->alterInfo('mailer_builder_info');
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    list($type, $subType) = array_pad(explode('.', $plugin_id), 2, NULL);

    if ($definition['has_entity']) {
      if ($entity_type = $this->entityTypeManager->getDefinition($type, FALSE)) {
        $definition['label'] = $entity_type->getLabel();
        $definition['provider'] = $entity_type->getProvider();
      }
    }
    else {
      if ($this->moduleHandler->moduleExists($type)) {
        $definition['label'] = $this->moduleHandler->getName($type);
        $definition['provider'] = $type;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFilteredDefinitions($consumer, $contexts = NULL, array $extra = []) {
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      // Filter by entity type.
      if (preg_match('|^type\.([\w_]+)|', $plugin_id, $matches)) {
        $definitions[$matches[1]] = $definition;
      }
    }

    return $definitions;
  }

}
