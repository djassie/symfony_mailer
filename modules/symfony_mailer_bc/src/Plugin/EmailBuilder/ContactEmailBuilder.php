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
    $subject = $contact_message->getSubject();
    $site_name = \Drupal::config('system.site')->get('name');

    if ($form = $params['contact_form']) {
      // Site form.
      $form_name = $form->label();
      $email->setSubject($this->t('[@form] @subject', ['@form' => $form_name, '@subject' => $subject]))
        ->setVariable('form', $form_name)
        ->setVariable('form_url', Url::fromRoute('<current>')->toString());
    }
    else {
      // Personal form.
      $email->setSubject($this->t('[@site-name] @subject', ['@site-name' => $site_name, 'subject' => $subject]))
        ->setVariable('recipient_name', $params['recipient']->getDisplayName())
        ->setVariable('recipient_edit_url', $params['recipient']->toUrl('edit-form')->toString());
    }

    if ($email->getSubType() == 'page_autoreply') {
      $email->setBody($params['contact_form']->getReply());
    }
    else {
      /** @var \Drupal\user\UserInterface $sender */
      $sender = $params['sender'];

      $email->appendBodyEntity($contact_message, 'mail')
        ->addLibrary('symfony_mailer_bc/contact')
        ->setVariable('site_name', $site_name)
        ->setVariable('sender_name', $sender->getDisplayName())
        ->setVariable('sender_url', $sender->isAuthenticated() ? $sender->toUrl('canonical')->toString() : $sender->getEmail());
    }
  }

}
