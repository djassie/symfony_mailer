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
    $key = $email->getSubType();
    if ($key == 'subscribe_combined') {
      $key = 'confirm_combined';
    }

    $config = \Drupal::config('simplenews.settings');
    $body = [
      '#type' => 'processed_text',
      '#text' => $config->get("subscription.{$key}_body"),
      '#format' => $config->get('subscription.text_format') ?: NULL,
    ];

    $email->setSubject($config->get("subscription.{$key}_subject"))
      ->setBody($body)
      ->addBuilder('token_replace', ['data' => $email->getParam('context'), 'pre_render' => TRUE]);
  }

}
