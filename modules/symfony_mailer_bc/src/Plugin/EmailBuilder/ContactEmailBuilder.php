<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for contact module personal forms.
 *
 * @EmailBuilder(
 *   id = "type.contact",
 *   label = @Translation("Email Builder for contact module"),
 *   sub_types = {
 *     "mail" = @Translation("Message"),
 *     "copy" = @Translation("Sender copy"),
 *   },
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class ContactEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    parent::build($email);
    $recipient = $email->getParams()['recipient'];

    $email->setVariable('recipient_name', $recipient->getDisplayName())
      ->setVariable('recipient_edit_url', $recipient->toUrl('edit-form')->toString());
  }

}
