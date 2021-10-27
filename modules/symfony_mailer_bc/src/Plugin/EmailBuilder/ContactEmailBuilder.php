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

    $variables = [
      '@site-name' => \Drupal::config('system.site')->get('name'),
      '@subject' => $contact_message->getSubject(),
      '@form' => !empty($params['contact_form']) ? $params['contact_form']->label() : NULL,
      '@form-url' => Url::fromRoute('<current>')->toString(),
      '@sender-name' => $sender->getDisplayName(),
    ];
    if ($sender->isAuthenticated()) {
      $variables['@sender-url'] = $sender->toUrl('canonical')->toString();
    }
    else {
      $variables['@sender-url'] = $params['sender']->getEmail();
    }

    $email->setParams($variables);

    switch ($email->getSubType()) {
      case 'page_mail':
      case 'page_copy':
        $email->setSubject($this->t('[@form] @subject', $variables));
        $email->appendBodyParagraph($this->t('<a href="@sender-url">@sender-name</a> sent a message using the contact form <a href="@form-url">@form</a>.', $variables));
        $build = \Drupal::entityTypeManager()
          ->getViewBuilder('contact_message')
          ->view($contact_message, 'mail');
        $email->appendBody($build);
        break;

      case 'page_autoreply':
        $email->setSubject($this->t('[@form] @subject', $variables));
        $email->setBody($params['contact_form']->getReply());
        break;

      case 'user_mail':
      case 'user_copy':
        $variables += [
          '@recipient-name' => $params['recipient']->getDisplayName(),
          '@recipient-edit-url' => $params['recipient']->toUrl('edit-form')->toString(),
        ];
        $email->setSubject($this->t('[@site-name] @subject', $variables));
        $email->appendBodyParagraph($this->t('Hello @recipient-name,', $variables));
        $email->appendBodyParagraph($this->t('<a href="@sender-url">@sender-name</a> has sent you a message via your contact form at @site-name.', $variables));
        $email->appendBodyParagraph($this->t('If you don\'t want to receive such emails, you can <a href="@recipient-edit-url">change your settings</a>.', $variables));
        $build = \Drupal::entityTypeManager()
          ->getViewBuilder('contact_message')
          ->view($contact_message, 'mail');
        $email->appendBody($build);
        break;
    }
  }

}
