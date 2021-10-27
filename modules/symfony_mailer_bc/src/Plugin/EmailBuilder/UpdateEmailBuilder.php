<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

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
    $email->setSubject($this->t('New release(s) available for @site_name', ['@site_name' => \Drupal::config('system.site')->get('name')]));
    $variables = [
      '@status' => Url::fromRoute('update.status')->toString(),
      '@manager' => Url::fromRoute('update.report_update')->toString(),
      '@settings' => Url::fromRoute('update.settings')->toString(),
    ];

    foreach ($email->getParams() as $msg_type => $msg_reason) {
      $email->appendBodyParagraph(_update_message_text($msg_type, $msg_reason));
    }
    $email->appendBodyParagraph($this->t('See the <a href="@status">available updates</a> page for more information.', $variables));


    if (_update_manager_access()) {
      $email->appendBodyParagraph($this->t('You can automatically install your missing updates using the <a href="@manager">Update manager</a>', $variables));
    }

    if (\Drupal::config('update.settings')->get('notification.threshold') == 'all') {
      $email->appendBodyParagraph($this->t('Your site is currently configured to send these emails when any updates are available. You can <a href="@settings">change your settings<a> to get notified only for security updates.', $variables));
    }
    else {
      $email->appendBodyParagraph($this->t('Your site is currently configured to send these emails only when security updates are available. You can <a href="@settings">change your settings<a> to get notified for any available updates.', $variables));
    }
  }

}
