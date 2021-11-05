<?php

use Drupal\Component\Render\MarkupInterface;
use Symfony\Component\Mime\Part\DataPart;

namespace Drupal\symfony_mailer;

interface BaseEmailInterface {

  /**
   * Gets all email builders.
   *
   * @return array
   *   Array of email builders.
   */
  public function getBuilders();

  /**
   * Gets the email type.
   *
   * If the email is associated with a config entity, then this is the entity
   * type, else it is the module name.
   *
   * @return string
   *   Email type.
   */
  public function getType();

  /**
   * Gets the email sub-type.
   *
   * The sub-type is a 'key' to distinguish different categories of email with
   * the same type. Emails with the same sub-type are all built in the same
   * way, differently from other sub-types.
   *
   * @return string
   *   Email sub-type.
   */
  public function getSubType();

  /**
   * Gets the associated config entity.
   *
   * The ID of this entity can be used to select a specific template or apply
   * specific policy configuration.
   *
   * @return ?\Drupal\Core\Config\Entity\ConfigEntityInterface.
   *   Entity, or NULL if there is no associated entity.
   */
  public function getEntity();

  /**
   * Gets an array of 'suggestions'.
   *
   * The suggestions are used to select email builders, templates and policy
   * configuration in based on email type, sub-type and associated entity ID.
   *
   * @param string $initial
   *   The initial suggestion.
   * @param string $join
   *   The 'glue' to join each suggestion part with.
   *
   * @return array
   *   Suggestions, formed by taking the initial value and incrementally adding
   *   the type, sub-type and entity ID.
   */
  public function getSuggestions(string $initial, string $join);

  /**
   * Gets the langcode.
   *
   * @return string
   *   Language code to use to compose the email.
   */
  public function getLangcode();

  /**
   * Gets parameters used for building the email.
   *
   * @return array
   *   An array of keyed objects.
   */
  public function getParams();

  /**
   * Gets a parameter used for building the email.
   *
   * @param string $key
   *   Parameter key to get.
   *
   * @return mixed
   *   Parameter value.
   */
  public function getParam(string $key);

  /**
   * Adds an asset library to use as mail CSS.
   *
   * @param string $library
   *   Library name, in the form "THEME/LIBRARY".
   *
   * @return $this
   */
  public function addLibrary(string $library);

  /**
   * Gets the libraries to use as mail CSS.
   *
   * @return array
   *   Array of library names, in the form "THEME/LIBRARY".
   */
  public function getLibraries();

}
