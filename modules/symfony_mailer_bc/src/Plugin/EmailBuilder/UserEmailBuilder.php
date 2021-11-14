<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Email Builder plug-in for user module.
 *
 * @EmailBuilder(
 *   id = "type.user",
 *   label = @Translation("Email Builder for user module"),
 *   sub_types = {
 *     "cancel_confirm",
 *     "password_reset",
 *     "register_admin_created",
 *     "register_no_approval_required",
 *     "register_pending_approval",
 *     "register_pending_approval_admin",
 *     "status_activated",
 *     "status_blocked",
 *     "status_canceled",
 *   },
 * )
 *
 * @todo Notes for adopting Symfony Mailer into Drupal core. This builder can
 * set langcode, to, reply-to so the calling code doesn't need to.
 */
class UserEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $token_options = ['callback' => 'user_mail_tokens', 'clear' => TRUE];
    $email->addBuilder('mailer_token_replace', ['options' => $token_options]);
  }

}
