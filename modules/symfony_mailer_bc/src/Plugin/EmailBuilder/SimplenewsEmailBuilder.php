<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailProcessorBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for simplenews module.
 *
 * @EmailBuilder(
 *   id = "simplenews",
 *   sub_types = {
 *     "subscribe" = @Translation("Subscription confirmation"),
 *     "validate" = @Translation("Validate"),
 *   },
 * )
 */
class SimplenewsEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(UnrenderedEmailInterface $email) {
    $email->addProcessor('mailer_token_replace', ['data' => $email->getParam('context')]);
  }

}
