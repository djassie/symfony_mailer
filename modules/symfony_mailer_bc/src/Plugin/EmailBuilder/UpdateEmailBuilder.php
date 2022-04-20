<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\symfony_mailer\EmailFactoryInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailBuilderBase;
use Drupal\update\UpdateManagerInterface;

/**
 * Defines the Email Builder plug-in for update module.
 *
 * @EmailBuilder(
 *   id = "update",
 *   sub_types = { "status_notify" = @Translation("Available updates") },
 * )
 */
class UpdateEmailBuilder extends EmailBuilderBase {

  use MailerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function fromArray(EmailFactoryInterface $factory, array $message) {
    return $factory->newModuleEmail($message['module'], $message['key']);
  }

  /**
   * {@inheritdoc}
   */
  public function build(EmailInterface $email) {
    $update_config = $this->helper()->config()->get('update.settings');
    $notify_all = ($update_config->get('notification.threshold') == 'all');
    \Drupal::moduleHandler()->loadInclude('update', 'install');
    $requirements = update_requirements('runtime');

    foreach (['core', 'contrib'] as $report_type) {
      $status = $requirements["update_$report_type"];
      if (isset($status['severity'])) {
        if ($status['severity'] == REQUIREMENT_ERROR || ($notify_all && $status['reason'] == UpdateManagerInterface::NOT_CURRENT)) {
          $messages[] = _update_message_text($report_type, $status['reason']);
        }
      }
    }

    $site_name = \Drupal::config('system.site')->get('name');
    // Set the account from the recipient to set langcode.
    $email->setAccount()
      ->setVariable('site_name', $site_name)
      ->setVariable('update_status', Url::fromRoute('update.status')->toString())
      ->setVariable('update_settings', Url::fromRoute('update.settings')->toString())
      ->setVariable('messages', $messages);

    if (Settings::get('allow_authorize_operations', TRUE)) {
      $email->setVariable('update_manager', Url::fromRoute('update.report_update')->toString());
    }
  }

}
