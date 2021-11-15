<?php

namespace Drupal\symfony_mailer;

/**
 * Provides a dynamic Iterator for EmailsBuilders.
 *
 * This iterator allows adding items during iteration.
 */
class EmailBuilderIterator implements \Iterator {

  protected $builders;
  protected $function;
  protected $position = 0;

  /**
   * Constructs the Email iterator object.
   *
   * @param \Drupal\symfony_mailer\EmailBuilderInterface[] $builders
   *   Array of email builders.
   * @param string $function
   *   The function being called, either 'build' or 'adjust'.
   */
  public function __construct(array $builders, string $function) {
    $this->builders = $builders;
    $this->function = $function;
    $this->sort();
  }

  /**
   * Adds an email builder to the iteration.
   *
   * @param \Drupal\symfony_mailer\EmailBuilderInterface $builder
   */
  public function add(EmailBuilderInterface $builder) {
    $this->builders[] = $builder;
    $this->sort();
    $key = array_search($builder, $this->builders, TRUE);
    if ($key <= $this->position) {
      $this->position++;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->position = 0;
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->builders[$this->position];
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->position;
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    $this->position++;
  }

  /**
   * {@inheritdoc}
   */
  public function valid() {
    return isset($this->builders[$this->position]);
  }

  /**
   * Sorts an array of email builders by weight, lowest first.
   */
  protected function sort() {
    usort($this->builders, function($a, $b) {
      return $a->getWeight($this->function) <=> $b->getWeight($this->function);
    });
  }

}
