<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
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
class CommerceEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $email->setSubject($email->getParam('subject'))
      ->setBody($email->getParam('body'));
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    if ($from = $email->getParam('from')) {
      // @todo This respects the email address of the store, but it loses the
      // display name.
      $email->getInner()->from($from);
    }
  }

}
