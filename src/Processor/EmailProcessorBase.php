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
   * Gets the weight of the email processor.
   *
   * @param int $phase
   *   The phase that will run, one of the EmailInterface::PHASE_ constants.
   *
   * @return int
   *   The weight.
   */
  protected function getWeight(int $phase) {
    $weight = $this->getPluginDefinition()['weight'] ?? static::DEFAULT_WEIGHT;
    return is_array($weight) ? $weight[$phase] : $weight;
  }

}
