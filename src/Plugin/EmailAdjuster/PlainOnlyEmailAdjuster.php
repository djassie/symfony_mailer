<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\symfony_mailer\EmailInterface;

/**
 * Defines the Plain text only Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_plain_only",
 *   label = @Translation("Plain text only"),
 *   description = @Translation("Sends email as plain text only."),
 *   weight = 810,
 * )
 */
class PlainOnlyEmailAdjuster extends EmailAdjusterBase {

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
    $email->setHtmlBody(NULL);
  }

}
