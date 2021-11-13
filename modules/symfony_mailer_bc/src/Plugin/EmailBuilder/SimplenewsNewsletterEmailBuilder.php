<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for simplenews_newsletter entity.
 *
 * @EmailBuilder(
 *   id = "simplenews_newsletter",
 *   label = @Translation("Email Builder for simplenews newsletters"),
 *   sub_types = { "node", "test" },
 *   has_entity = TRUE,
 * )
 *
 * @todo Notes for adopting Symfony Mailer into simplenews. Can remove the
 * MailBuilder class, and many methods of MailEntity.
 */
class SimplenewsNewsletterEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    /** @var \Drupal\simplenews\Mail\MailEntity $mail */
    $mail = $email->getParam('simplenews_mail');
    $email->setSubject($mail->getSubject())
      ->setBody($mail->getBody())
      ->addBuilder('token_replace');
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $headers = $email->getInner()->getHeaders();
    $headers->addTextHeader('Precedence', 'bulk');
    if ($unsubscribe_url = \Drupal::token()->replace('[simplenews-subscriber:unsubscribe-url]', $email->getParams())) {
      $headers->addTextHeader('List-Unsubscribe', "<$unsubscribe_url>");
    }
  }

}
