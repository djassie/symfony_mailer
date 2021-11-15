<?php

namespace Drupal\symfony_mailer;

/**
 * Provides a dynamic Iterator for Email Processors.
 *
 * This iterator allows adding items during iteration.
 */
class EmailProcessorIterator implements \Iterator {

  protected $processors;
  protected $function;
  protected $position = 0;

  /**
   * Constructs the Email iterator object.
   *
   * @param \Drupal\symfony_mailer\EmailProcessorInterface[] $processors
   *   Array of email processors.
   * @param string $function
   *   The function being called, either 'build' or 'adjust'.
   */
  public function __construct(array $processors, string $function) {
    $this->processors = $processors;
    $this->function = $function;
    $this->sort();
  }

  /**
   * Adds an email builder to the iteration.
   *
   * @param \Drupal\symfony_mailer\EmailProcessorInterface $builder
   */
  public function add(EmailProcessorInterface $builder) {
    $this->processors[] = $builder;
    $this->sort();
    $key = array_search($builder, $this->processors, TRUE);
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
    return $this->processors[$this->position];
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
    return isset($this->processors[$this->position]);
  }

  /**
   * Sorts an array of email processors by weight, lowest first.
   */
  protected function sort() {
    usort($this->processors, function($a, $b) {
      return $a->getWeight($this->function) <=> $b->getWeight($this->function);
    });
  }

}
