<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\MailerPolicyImportInterface;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "user",
 *   sub_types = {
 *     "cancel_confirm" = @Translation("Account cancellation confirmation"),
 *     "password_reset" = @Translation("Password recovery"),
 *     "register_admin_created" = @Translation("Account created by administrator"),
 *     "register_no_approval_required" = @Translation("Registration confirmation (No approval required)"),
 *     "register_pending_approval" = @Translation("Registration confirmation (Pending approval)"),
 *     "register_pending_approval_admin" = @Translation("Admin (user awaiting approval)"),
 *     "status_activated" = @Translation("Account activation"),
 *     "status_blocked" = @Translation("Account blocked"),
 *     "status_canceled" = @Translation("Account cancelled"),
 *   },
 *   common_adjusters = {"email_subject", "email_body"},
 *   import = @Translation("User email settings"),
 *   import_warning = @Translation("This overrides the default HTML messages with imported plain text versions."),
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class UserEmailBuilder extends EmailProcessorBase implements MailerPolicyImportInterface {

  use MailerHelperTrait;
  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    if ($email->getSubType() != 'register_pending_approval_admin') {
      $email->setTo($email->getParam('user')->getEmail());
    }
    $this->tokenOptions(['callback' => 'user_mail_tokens', 'clear' => TRUE]);
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $config_factory = $this->helper()->config();
    $notify = $config_factory->get('user.settings')->get('notify');
    $mail = $config_factory->get('user.mail')->get();
    unset($mail['langcode']);

    if ($mail_notification = $config_factory->get('system.site')->get('mail_notification')) {
      $notification_policy = $this->helper()->policyFromAddresses($this->helper()->parseAddress($mail_notification));
      $config['email_reply_to'] = $notification_policy;
      MailerPolicy::import("user", $config);
    }

    foreach ($mail as $sub_type => $values) {
      $config = [
        'email_subject' => ['value' => $values["subject"]],
        'email_body' => [
          'content' => [
            'value' => $values["body"],
            'format' => 'plain_text',
          ],
        ],
      ];
      if (isset($notify[$sub_type]) && !$notify[$sub_type]) {
        $config['email_skip_sending']['message'] = 'Notification disabled in settings';
      }
      if (($sub_type == 'register_pending_approval_admin') && isset($notification_policy)) {
        $config['email_to'] = $notification_policy;
      }
      MailerPolicy::import("user.$sub_type", $config);
    }
  }

}
