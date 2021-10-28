<?php

/**
 * @file
 * Documentation of Symfony Mailer hooks.
 */

use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Acts on an email message prior to building.
 *
 * The email is not yet built. Can alter the language or the configured email
 * builders.
 *
 * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
 *   The email.
 */
function hook_email_pre_build(UnrenderedEmailInterface $email) {
}

/**
 * Acts on an email message prior to rendering.
 *
 * The email is now fully built, and the body/subject can be altered.
 *
 * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
 *   The email.
 */
function hook_email_pre_render(UnrenderedEmailInterface $email) {
}

/**
 * Acts on an email message prior to sending.
 *
 * The email is now ready to send and any headers can be altered.
 *
 * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
 *   The email.
 */
function hook_email_pre_send(RenderedEmailInterface $email) {
}
