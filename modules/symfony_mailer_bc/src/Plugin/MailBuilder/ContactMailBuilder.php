<?php

namespace Drupal\symfony_mailer_bc\Plugin\MailBuilder;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\symfony_mailer\MailBuilderInterface;

/**
 * Defines the Mail Builder plug-in for contact module.
 *
 * @MailBuilder(
 *   id = "contact",
 *   label = @Translation("Mail Builder for contact module"),
 * )
 */
class ContactMailBuilder implements MailBuilderInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function mail($email, $key, $to, $langcode, $params) {
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

    $email->params($variables);

    switch ($key) {
      case 'page_mail':
      case 'page_copy':
        $email->subject($this->t('[@form] @subject', $variables));
        $email->appendParagraph($this->t("@sender-name (@sender-url) sent a message using the contact form at @form-url.", $variables));
        $build = \Drupal::entityTypeManager()
          ->getViewBuilder('contact_message')
          ->view($contact_message, 'mail');
        $email->appendContent($build);
        break;

      case 'page_autoreply':
        $email->subject($this->t('[@form] @subject', $variables));
        $email->content($params['contact_form']->getReply());
        break;

      case 'user_mail':
      case 'user_copy':
        $variables += [
          '@recipient-name' => $params['recipient']->getDisplayName(),
          '@recipient-edit-url' => $params['recipient']->toUrl('edit-form')->toString(),
        ];
        $email->subject($this->t('[@site-name] @subject', $variables));
        $email->appendParagraph($this->t('Hello @recipient-name,', $variables));
        $email->appendParagraph($this->t("@sender-name (@sender-url) has sent you a message via your contact form at @site-name.", $variables));
        $email->appendParagraph($this->t("If you don't want to receive such emails, you can change your settings at @recipient-edit-url.", $variables));
        $build = \Drupal::entityTypeManager()
          ->getViewBuilder('contact_message')
          ->view($contact_message, 'mail');
        $email->appendContent($build);
        break;
    }
  }

}
