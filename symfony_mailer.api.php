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
function hook_mailer_pre_build(UnrenderedEmailInterface $email) {
}

/**
 * Acts on an email message prior to rendering.
 *
 * The email is now fully built, and the body/subject can be altered.
 *
 * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
 *   The email.
 */
function hook_mailer_pre_render(UnrenderedEmailInterface $email) {
}

/**
 * Acts on an email message prior to sending.
 *
 * The email is now ready to send and any headers can be altered.
 *
 * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
 *   The email.
 */
function hook_mailer_pre_send(RenderedEmailInterface $email) {
}

// @todo Versions with __TYPE, __SUBTYPE

/**
 * Alters email builder plug-in definitions.
 *
 * @param array $email_builders
 *   An associative array of all email builder definitions, keyed by the ID.
 */
function hook_mailer_builder_info_alter(array &$email_builders) {
}

/**
 * Alters mailer transport plug-in definitions.
 *
 * @param array $mailer_transports
 *   An associative array of all mailer transport definitions, keyed by the ID.
 */
function hook_mailer_transport_info_alter(array &$mailer_transports) {
}
