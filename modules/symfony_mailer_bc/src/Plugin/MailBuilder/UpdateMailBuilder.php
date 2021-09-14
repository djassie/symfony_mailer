<?php

namespace Drupal\symfony_mailer_bc\Plugin\MailBuilder;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\symfony_mailer\MailBuilderInterface;

/**
 * Defines the Mail Builder plug-in for update module.
 *
 * @MailBuilder(
 *   id = "update",
 *   label = @Translation("Mail Builder for update module"),
 * )
 */
class UpdateMailBuilder implements MailBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function mail($email, $key, $to, $langcode, $params) {
    $language = \Drupal::languageManager()->getLanguage($langcode);
    $email->subject($this->t('New release(s) available for @site_name', ['@site_name' => \Drupal::config('system.site')->get('name')], ['langcode' => $langcode]));
    foreach ($params as $msg_type => $msg_reason) {
      $email->appendParagraph(_update_message_text($msg_type, $msg_reason, $langcode));
    }
    $email->appendParagraph($this->t('See the available updates page for more information:', [], ['langcode' => $langcode]) . "\n" . Url::fromRoute('update.status', [], ['absolute' => TRUE, 'language' => $language])->toString());
    if (_update_manager_access()) {
      $email->appendParagraph($this->t('You can automatically install your missing updates using the Update manager:', [], ['langcode' => $langcode]) . "\n" . Url::fromRoute('update.report_update', [], ['absolute' => TRUE, 'language' => $language])->toString());
    }
    $settings_url = Url::fromRoute('update.settings', [], ['absolute' => TRUE])->toString();
    if (\Drupal::config('update.settings')->get('notification.threshold') == 'all') {
      $email->appendParagraph($this->t('Your site is currently configured to send these emails when any updates are available. To get notified only for security updates, @url.', ['@url' => $settings_url]));
    }
    else {
      $email->appendParagraph($this->t('Your site is currently configured to send these emails only when security updates are available. To get notified for any available updates, @url.', ['@url' => $settings_url]));
    }
  }

}
