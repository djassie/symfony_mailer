<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for contact module personal forms.
 *
 * @EmailBuilder(
 *   id = "contact",
 *   label = @Translation("Email Builder for contact module"),
 * )
 */
class ContactEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    parent::build($email);
    $recipient = $email->getParams()['recipient'];

    $email->setSubject('[[site:name]] [variables:subject]')
      ->setVariable('recipient_name', $recipient->getDisplayName())
      ->setVariable('recipient_edit_url', $recipient->toUrl('edit-form')->toString());
  }

}
