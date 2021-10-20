<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\StringTranslation\StringTranslationTrait;
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

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $email->setSubject($this->t('New release(s) available for @site_name', ['@site_name' => \Drupal::config('system.site')->get('name')]));

    foreach ($email->getParams() as $msg_type => $msg_reason) {
      $email->appendBodyParagraph(_update_message_text($msg_type, $msg_reason, $langcode));
    }
    $email->appendBodyParagraph($this->t('See the available updates page for more information:') . "\n" . Url::fromRoute('update.status')->toString());

    if (_update_manager_access()) {
      $email->appendBodyParagraph($this->t('You can automatically install your missing updates using the Update manager:') . "\n" . Url::fromRoute('update.report_update')->toString());
    }

    $settings_url = Url::fromRoute('update.settings')->toString();
    if (\Drupal::config('update.settings')->get('notification.threshold') == 'all') {
      $email->appendBodyParagraph($this->t('Your site is currently configured to send these emails when any updates are available. To get notified only for security updates, @url.', ['@url' => $settings_url]));
    }
    else {
      $email->appendBodyParagraph($this->t('Your site is currently configured to send these emails only when security updates are available. To get notified for any available updates, @url.', ['@url' => $settings_url]));
    }
  }

}
