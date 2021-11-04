<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Url;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for contact module.
 *
 * @EmailBuilder(
 *   id = "contact",
 *   label = @Translation("Email Builder for contact module"),
 * )
 */
class ContactEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $params = $email->getParams();
    $contact_message = $params['contact_message'];
    /** @var \Drupal\user\UserInterface $sender */
    $sender = $params['sender'];
    $site_name = \Drupal::config('system.site')->get('name');
    $form = !empty($params['contact_form']) ? $params['contact_form']->label() : NULL;

    $subject_variables = [
      '@site-name' => $site_name,
      '@form' => $form,
      '@subject' => $contact_message->getSubject(),
    ];

    $key = $email->getSubType();

    if ($key == 'page_autoreply') {
      $email->setSubject($this->t('[@form] @subject', $subject_variables))
        ->setBody($params['contact_form']->getReply());
      return;
    }

    $email->appendBodyEntity($contact_message, 'mail')
      ->addLibrary('symfony_mailer_bc/contact')
      ->setParam('site_name', $site_name)
      ->setParam('sender_name', $sender->getDisplayName())
      ->setParam('sender_url', $sender->isAuthenticated() ? $sender->toUrl('canonical')->toString() : $sender->getEmail());

    switch ($key) {
      case 'page_mail':
      case 'page_copy':
        $email->setSubject($this->t('[@form] @subject', $subject_variables))
          ->setParam('form', $form)
          ->setParam('form_url', Url::fromRoute('<current>')->toString());
        break;

      case 'user_mail':
      case 'user_copy':
        $email->setSubject($this->t('[@site-name] @subject', $subject_variables))
          ->setParam('recipient_name', $params['recipient']->getDisplayName())
          ->setParam('recipient_edit_url', $params['recipient']->toUrl('edit-form')->toString());
        break;
    }
  }

}
