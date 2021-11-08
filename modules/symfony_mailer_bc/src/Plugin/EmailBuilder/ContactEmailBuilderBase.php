<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines a base class for contact module email builders.
 * )
 */
class ContactEmailBuilderBase extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    /** @var \Drupal\user\UserInterface $sender */
    $sender = $email->getParam('sender');
    $contact_message = $email->getParam('contact_message');

    $email->appendBodyEntity($contact_message, 'mail')
      ->addLibrary('symfony_mailer_bc/contact')
      ->addBuilder('token_replace')
      ->setVariable('subject', $contact_message->getSubject())
      ->setVariable('sender_name', $sender->getDisplayName())
      ->setVariable('sender_url', $sender->isAuthenticated() ? $sender->toUrl('canonical')->toString() : $sender->getEmail());
  }

}
