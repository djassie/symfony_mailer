<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for update module.
 *
 * @EmailBuilder(
 *   id = "update",
 *   label = @Translation("Email Builder for update module"),
 * )
 */
class UpdateEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    foreach ($email->getParams() as $msg_type => $msg_reason) {
      $messages[] = _update_message_text($msg_type, $msg_reason);
    }

    $site_name = \Drupal::config('system.site')->get('name');
    $email->setSubject($this->t('New release(s) available for @site_name', ['@site_name' => $site_name]))
      ->setParam('site_name', $site_name)
      ->setParam('update_status', Url::fromRoute('update.status')->toString())
      ->setParam('update_settings', Url::fromRoute('update.settings')->toString())
      ->setParam('messages', $messages);

    if (Settings::get('allow_authorize_operations', TRUE)) {
      $email->setParam('update_manager', Url::fromRoute('update.report_update')->toString());
    }
  }

}
