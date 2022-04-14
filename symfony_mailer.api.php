<?php

/**
 * @file
 * Documentation of Symfony Mailer hooks.
 */

use Drupal\symfony_mailer\EmailInterface;

/**
 * Acts on an email message initialization.
 *
 * @param \Drupal\symfony_mailer\EmailInterface $email
 *   The email.
 */
function hook_mailer_init(EmailInterface $email) {
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
