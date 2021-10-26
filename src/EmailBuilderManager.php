<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Provides the email builder plugin manager.
 */
class EmailBuilderManager extends DefaultPluginManager {

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
    parent::__construct('Plugin/EmailBuilder', $namespaces, $module_handler, 'Drupal\symfony_mailer\EmailBuilderInterface', 'Drupal\symfony_mailer\Annotation\EmailBuilder');
    $this->setCacheBackend($cache_backend, 'symfony_mailer_builder_plugins');
  }

  /**
   * Sorts an array of email builders by weight, lowest first.
   *
   * @param \Drupal\symfony_mailer\EmailBuilderInterface[] $builders
   *   An array of email builders to sort.
   */
  public function sort(array &$builders) {
    uasort($builders, [$this, 'compare']);
  }

  /**
   * Compares two email builders for sorting by weight.
   *
   * @param \Drupal\symfony_mailer\EmailBuilderInterface $a
   *   First email builder to compare.
   * @param \Drupal\symfony_mailer\EmailBuilderInterface $b
   *   Second email builder to compare.
   *
   * @return int
   *   Comparison result suitable for use in uasort.
   */
  protected function compare(EmailBuilderInterface $a, EmailBuilderInterface $b) {
    return $a->getWeight() - $b->getWeight();
  }

}
