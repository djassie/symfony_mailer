<?php

namespace Drupal\symfony_mailer_test;

use Drupal\Core\DestructableInterface;
use Drupal\Core\State\StateInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Processor\EmailProcessorInterface;

/**
 * Tracks sent emails for testing.
 */
class MailerTestService implements MailerTestServiceInterface, EmailProcessorInterface, DestructableInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The emails that have been sent.
   *
   * @var \Drupal\symfony_mailer\EmailInterface[]
   */
  protected $emails = [];

  /**
   * Constructs the MailerTestService.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
    if ($existing_emails = $this->state->get(self::STATE_KEY, [])) {
      throw new \Exception(count($existing_emails) . ' emails have not been checked.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function destruct() {
    if ($this->emails) {
      $this->state->set(self::STATE_KEY, $this->emails);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function init(EmailInterface $email) {
    $email->addProcessor([$this, 'postRender'], EmailInterface::PHASE_POST_RENDER, 10000, static::class);
    $email->addProcessor([$this, 'postSend'], EmailInterface::PHASE_POST_SEND, 10000, static::class);
  }

  /**
   * Post-render function.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email.
   */
  public function postRender(EmailInterface $email) {
    $email->setTransportDsn('null://default');
  }

  /**
   * Post-send function.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email.
   */
  public function postSend(EmailInterface $email) {
    $this->emails[] = $email;
  }

}
