<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the To Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "email_to",
 *   label = @Translation("To"),
 *   description = @Translation("Sets the email to header."),
 *   weight = 200,
 * )
 */
class ToEmailAdjuster extends AddressAdjusterBase {

  /**
   * The name of the associated header.
   */
  protected const NAME = 'To';

  /**
   * {@inheritdoc}
   */
  public function init(EmailInterface $email) {
    // The to address can be used to default the recipient account. Ensure that
    // it is set early enough to do this.
    $email->addProcessor($this->getPluginId(), EmailInterface::PHASE_BUILD, [$this, 'postRender'], $this->getWeight(EmailInterface::PHASE_BUILD));
  }

}
