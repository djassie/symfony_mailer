<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\contact\Entity\ContactForm;
use Drupal\Core\Url;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\MailerPolicyImportInterface;

/**
 * Defines the Email Builder plug-in for contact module page forms.
 *
 * @EmailBuilder(
 *   id = "contact_form",
 *   sub_types = {
 *     "mail" = @Translation("Message"),
 *     "copy" = @Translation("Sender copy"),
 *     "autoreply" = @Translation("Auto-reply"),
 *   },
 *   has_entity = TRUE,
 *   common_adjusters = {"email_subject", "email_from"},
 *   import = @Translation("Contact form recipients"),
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class ContactPageEmailBuilder extends ContactEmailBuilderBase implements MailerPolicyImportInterface {

  use MailerHelperTrait;

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    parent::preRender($email);
    $email->setVariable('form', $email->getEntity()->label())
      ->setVariable('form_url', Url::fromRoute('<current>')->toString());

    if ($email->getSubType() == 'autoreply') {
      $email->setBody($email->getParam('contact_form')->getReply());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $helper = $this->helper();

    foreach (ContactForm::loadMultiple() as $id => $form) {
      if ($id != 'personal') {
        $addresses = $helper->parseAddress(implode(',', $form->getRecipients()));
        $config['email_to'] = $helper->policyFromAddresses($addresses);
        MailerPolicy::import("contact_form.mail.$id", $config);
      }
    }
  }

}
