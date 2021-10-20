<?php

use Drupal\Component\Render\MarkupInterface;
use Symfony\Component\Mime\Part\DataPart;

namespace Drupal\symfony_mailer;

interface BaseEmailInterface {

  /**
   * Gets alter callbacks.
   *
   * @param string $type
   *   The callback type: pre or post.
   *
   * @return array
   *   Array of callbacks.
   */
  public function getAlter(string $type);

  /**
   * Gets the message key.
   *
   * @return array
   *   Message key array, in the form [MODULE, TYPE, INSTANCE].
   */
  public function getKey();

  /**
   * Gets an array of 'suggestions' for the message key.
   *
   * @param string $initial
   *   The initial suggestion.
   * @param string $join
   *   The 'glue' to join each part of the key array with.
   *
   * @return array
   *   Suggestions, formed by taking the initial part and incrementally adding
   *   each part of the key.
   */
  public function getKeySuggestions(string $initial, string $join);

  /**
   * Gets the langcode.
   *
   * @return string
   *   Language code to use to compose the email.
   */
  public function getLangcode();

  /**
   * Gets parameters to pass to the email template and for token replacement.
   *
   * @return array
   *   An array of keyed objects.
   */
  public function getParams();

  /**
   * Gets a parameter to pass to the email template and for token replacement.
   *
   * @param string $key
   *   Parameter key to get.
   *
   * @return mixed
   *   Parameter value.
   */
  public function getParam(string $key);

}
