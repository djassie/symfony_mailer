<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Core\Url;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for contact module page forms.
 *
 * @EmailBuilder(
 *   id = "contact_form",
 *   label = @Translation("Email Builder for contact module"),
 * )
 */
class ContactPageEmailBuilder extends ContactEmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    parent::build($email);
    $email->setSubject('[[variables:form]] [variables:subject]')
      ->setVariable('form', $email->getEntity()->label())
      ->setVariable('form_url', Url::fromRoute('<current>')->toString());

    if ($email->getSubType() == 'autoreply') {
      $email->setBody($email->getParam('contact_form')->getReply());
    }
  }

}
