<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\symfony_mailer\Entity\MailerTransport;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\MailerPolicyImportInterface;

/**
 * Defines the Email Builder plug-in for swiftmailer module.
 *
 * Dummy class for config import only.
 *
 * @EmailBuilder(
 *   id = "swiftmailer",
 *   migrate = @Translation("Swiftmailer transport settings"),
 * )
 */
class SwiftmailerEmailBuilder extends EmailProcessorBase implements MailerPolicyImportInterface {

  use MailerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function import() {
    $settings = $this->helper()->config()->get('swiftmailer.transport')->get();

    if ($settings['transport'] == SWIFTMAILER_TRANSPORT_SMTP) {
      $config = [
        'user' => $settings['smtp_credentials']['swiftmailer']['username'],
        'pass' => $settings['smtp_credentials']['swiftmailer']['password'],
        'host' => $settings['smtp_host'],
        'port' => $settings['smtp_port'],
      ];

      $transport = MailerTransport::load('swiftmailer') ?? MailerTransport::create(['id' => 'swiftmailer']);
      $transport->setPluginId('smtp')
        ->set('label', 'Imported from swiftmailer')
        ->set('configuration', $config)
        ->setAsDefault()
        ->save();
    }
  }

}
