<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Plugin\PluginBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

class EmailProcessorBase extends PluginBase implements EmailProcessorInterface {

  const DEFAULT_WEIGHT = 500;

  /**
   * {@inheritdoc}
   */
  public function preRender(UnrenderedEmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(string $function) {
    $weight = $this->getPluginDefinition()['weight'] ?? static::DEFAULT_WEIGHT;
    return is_array($weight) ? $weight[$function] : $weight;
  }

}
