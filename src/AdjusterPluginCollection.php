<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultLazyPluginCollection;

/**
 * A collection of email adjusters.
 */
class AdjusterPluginCollection extends DefaultLazyPluginCollection {

  /**
   * {@inheritdoc}
   */
  protected function initializePlugin($instance_id) {
    $configuration = $this->configurations[$instance_id];
    $this->set($instance_id, $this->manager->createInstance($instance_id, $configuration));
  }

}
