<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\PluginBase;

class EmailBuilderBase extends PluginBase implements EmailBuilderInterface {

  const DEFAULT_WEIGHT = 500;

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(string $function) {
    $weight = $this->getPluginDefinition()['weight'] ?? static::DEFAULT_WEIGHT;
    return is_array($weight) ? $weight[$function] : $weight;
  }

}
