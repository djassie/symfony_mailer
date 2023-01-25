<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportFactoryInterface;

/**
 * Provides the transport factory manager.
 */
class TransportFactoryManager implements TransportFactoryManagerInterface {

  /**
   * List of transport factories.
   *
   * @var \Symfony\Component\Mailer\Transport\TransportFactoryInterface[]
   */
  protected $factories;

  /**
   * {@inheritdoc}
   */
  public function addFactory(TransportFactoryInterface $factory) {
    $this->factories[] = $factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getFactories() {

    foreach (Transport::getDefaultFactories() as $f) {
      yield $f;
    }

    foreach ($this->factories as $f) {
      yield $f;
    }
  }

}
