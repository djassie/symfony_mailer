<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\simplenews\Entity\Newsletter;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailProcessorBase;
use Drupal\symfony_mailer\Processor\MailerPolicyImportInterface;
use Drupal\symfony_mailer\Processor\TokenProcessorTrait;
use Symfony\Component\Mime\Address;

/**
 * Defines the Email Builder plug-in for simplenews_newsletter entity.
 *
 * @EmailBuilder(
 *   id = "simplenews_newsletter",
 *   sub_types = {
 *     "node" = @Translation("Issue"),
 *   },
 *   has_entity = TRUE,
 *   common_adjusters = {"email_subject", "email_from"},
 *   import = @Translation("Simplenews newsletter settings"),
 * )
 *
 * @todo Notes for adopting Symfony Mailer into simplenews. Can remove the
 * MailBuilder class, and many methods of MailEntity.
 */
class SimplenewsNewsletterEmailBuilder extends EmailProcessorBase implements MailerPolicyImportInterface {

  use MailerHelperTrait;
  use TokenProcessorTrait;
  // @todo Maybe only replace in the subject as body already done?

  /**
   * {@inheritdoc}
   */
  public function preRender(EmailInterface $email) {
    /** @var \Drupal\simplenews\Mail\MailEntity $mail */
    $mail = $email->getParam('simplenews_mail');
    $this->tokenData($mail->getTokenContext());
    $email->setBody($mail->getBody())
      ->addTextHeader('Precedence', 'bulk');

    if ($unsubscribe_url = \Drupal::token()->replace('[simplenews-subscriber:unsubscribe-url]', $email->getParams(), ['clear' => TRUE])) {
      $email->addTextHeader('List-Unsubscribe', "<$unsubscribe_url>");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function import() {
    $helper = $this->helper();

    $settings = $this->helper()->config()->get('simplenews.settings');
    $from = new Address($settings->get('newsletter.from_address'), $settings->get('newsletter.from_name'));
    $config['email_from'] = $helper->policyFromAddresses([$from]);
    $config['email_subject']['value'] = '[[simplenews-newsletter:name]] [node:title]';
    MailerPolicy::import('simplenews_newsletter', $config);

    foreach (Newsletter::loadMultiple() as $id => $newsletter) {
      $from = new Address($newsletter->from_address, $newsletter->from_name);
      $config['email_from'] = $helper->policyFromAddresses([$from]);
      $config['email_subject']['value'] = $newsletter->subject;
      MailerPolicy::import("simplenews_newsletter.node.$id", $config);
    }
  }

}
