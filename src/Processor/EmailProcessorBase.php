<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Core\Plugin\PluginBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the base class for EmailProcessorInterface implementations.
 */
class EmailProcessorBase extends PluginBase implements EmailProcessorInterface {

  /**
   * Mapping from phase to default function name.
   *
   * @var string[]
   */
  protected const FUNCTION_NAMES = [
    EmailInterface::PHASE_BUILD => 'build',
    EmailInterface::PHASE_PRE_RENDER => 'preRender',
    EmailInterface::PHASE_POST_RENDER => 'postRender',
  ];

  /**
   * {@inheritdoc}
   */
  public function init(EmailInterface $email) {
    foreach (self::FUNCTION_NAMES as $phase => $function) {
      if (method_exists($this, $function)) {
        $email->addProcessor($this->getPluginId(), $phase, [$this, $function], $this->getWeight($phase));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(int $phase) {
    $weight = $this->getPluginDefinition()['weight'] ?? EmailInterface::DEFAULT_WEIGHT;
    return is_array($weight) ? $weight[$phase] : $weight;
  }

}
