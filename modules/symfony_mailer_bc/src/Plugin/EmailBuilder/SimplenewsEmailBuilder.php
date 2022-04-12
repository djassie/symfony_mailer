<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\MailerPolicyImportInterface;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;

/**
 * Defines the Email Builder plug-in for simplenews module.
 *
 * @EmailBuilder(
 *   id = "simplenews",
 *   sub_types = {
 *     "subscribe" = @Translation("Subscription confirmation"),
 *     "validate" = @Translation("Validate"),
 *   },
 *   common_adjusters = {"email_subject", "email_body"},
 *   import = @Translation("Simplenews subscriber settings"),
 *   import_warning = @Translation("This overrides the default HTML messages with imported plain text versions."),
 * )
 */
class SimplenewsEmailBuilder extends EmailProcessorBase implements MailerPolicyImportInterface {

  use MailerHelperTrait;
  use TokenProcessorTrait;

  /**
   * {@inheritdoc}
   */
  public function preBuild(EmailInterface $email) {
    $subscriber = $email->getParam('simplenews_subscriber');
    $email->setTo($subscriber->getMail())
      ->setLangcode($subscriber->getLangcode());
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $subscription = $this->helper()->config()->get('simplenews.settings')->get('subscription');

    $convert = [
      'confirm_combined' => 'subscribe',
      'validate' => 'validate',
    ];

    foreach ($convert as $from => $to) {
      $config = [
        'email_subject' => ['value' => $subscription["{$from}_subject"]],
        'email_body' => ['value' => $subscription["{$from}_body"]],
      ];
      MailerPolicy::import("simplenews.$to", $config);
    }
  }

}
