<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\Entity\MailerTransport;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\MailerHelperTrait;

/**
 * Defines the Default headers Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_default_headers",
 *   label = @Translation("Default headers"),
 *   description = @Translation("Set default headers."),
 *   weight = 100,
 * )
 */
class DefaultsEmailAdjuster extends EmailAdjusterBase {

  use MailerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    $sender = $this->helper()->getSiteAddress();
    $theme = $email->getTheme();
    $email->setSender($sender)
      ->addTextHeader('X-Mailer', 'Drupal')
      ->addLibrary("$theme/email");

    if ($default_transport = MailerTransport::loadDefault()) {
      $email->setTransportDsn($default_transport->getDsn());
    }
  }

}
