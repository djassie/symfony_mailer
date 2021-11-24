<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailProcessorBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for commerce module.
 *
 * @EmailBuilder(
 *   id = "commerce",
  * )
 *
 * @todo Notes for adopting Symfony Mailer into commerce. It should be possible
 * to remove the MailHandler service. Classes such as OrderReceiptMail could
 * call directly to UnrenderedEmailInterface or even be converted to an
 * EmailBuilder. The commerce_order_receipt template could be retired,
 * switching instead to the email__commerce__order_receipt.
 */
class CommerceEmailBuilder extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function preRender(UnrenderedEmailInterface $email) {
    $email->setSubject($email->getParam('subject'))
      ->setBody($email->getParam('body'));
  }

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    if ($from = $email->getParam('from')) {
      $email->getInner()->from($from);
    }

    if ($bcc = $email->getParam('bcc')) {
      $email->getInner()->bcc($bcc);
    }
  }

}
