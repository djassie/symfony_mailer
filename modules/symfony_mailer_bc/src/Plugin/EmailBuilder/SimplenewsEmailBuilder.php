<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for simplenews module.
 *
 * @EmailBuilder(
 *   id = "simplenews",
 *   label = @Translation("Email Builder for simplenews module"),
 * )
 */
class SimplenewsEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $email->addBuilder('token_replace', ['data' => $email->getParam('context')]);
  }

}
