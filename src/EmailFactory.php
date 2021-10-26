<?php

namespace Drupal\symfony_mailer;

/**
 * Provides a factory for create email objects.
 */
class EmailFactory {

  /**
   * Creates an email object.
   *
   * @param array $key
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   * @param bool $legacy
   *   (Internal) If TRUE, add the legacy builder if no other builder is found.
   *
   * @return \Drupal\symfony_mailer\Email
   *   A new email object.
   */
  public function newEmail($key, $legacy = FALSE) {
    $email = Email::create(\Drupal::getContainer(), $key);

    foreach ($email->getKeySuggestions('', '.') as $id) {
      $email->addBuilder($id, [], TRUE);
    }

    if ($legacy && !$email->getBuilders()) {
      $email->addBuilder('__legacy');
    }

    $email->addBuilder('default_headers')
      ->addBuilder('url_to_absolute')
      ->addBuilder('html_to_text')
      ->addBuilder('inline_css');
    return $email;
  }

}
