<?php

/**
 * @file
 * Documentation of Symfony Mailer hooks.
 */

use Drupal\symfony_mailer\EmailInterface;

/**
 * Acts on email initialization.
 *
 * @param \Drupal\symfony_mailer\EmailInterface $email
 *   The email.
 */
function hook_mailer_init(EmailInterface $email) {
}

/**
 * Acts on email initialization for a specific email type.
 *
 * @param \Drupal\symfony_mailer\EmailInterface $email
 *   The email.
 */
function hook_mailer_TYPE_init(EmailInterface $email) {
}

/**
 * Acts on email message initialization for a specific email type and sub-type.
 *
 * @param \Drupal\symfony_mailer\EmailInterface $email
 *   The email.
 */
function hook_mailer_TYPE_SUBTYPE_init(EmailInterface $email) {
}

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
