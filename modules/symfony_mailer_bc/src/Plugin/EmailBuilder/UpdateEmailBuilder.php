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
 *   sub_types = { "status_notify" },
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
      ->setVariable('site_name', $site_name)
      ->setVariable('update_status', Url::fromRoute('update.status')->toString())
      ->setVariable('update_settings', Url::fromRoute('update.settings')->toString())
      ->setVariable('messages', $messages);

    if (Settings::get('allow_authorize_operations', TRUE)) {
      $email->setVariable('update_manager', Url::fromRoute('update.report_update')->toString());
    }
  }

}
