<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the interface for an Email.
 */
interface EmailInterface extends BaseEmailInterface {

  /**
   * The default weight for an email processor.
   *
   * Set processors and parameters.
   */
  const DEFAULT_WEIGHT = 500;

  /**
   * Initialisation phase: set the processing that will occur.
   *
   * Set processors and parameters.
   */
  const PHASE_INIT = 0;

  /**
   * Build phase: construct the email.
   *
   * Must not trigger any rendering because cannot yet rely on the correct
   * language, theme, and account. For example, must not cast a translatable
   * string into a plain string, or replace tokens.
   */
  const PHASE_BUILD = 1;

  /**
   * Pre-render phase: preparation for rendering.
   *
   * Not normally needed. Only if there is a rendering step that needs to be
   * done before the main rendering call.
   */
  const PHASE_PRE_RENDER = 2;

  /**
   * Post-render phase: adjusting of rendered output.
   *
   * Act on the rendered HTML, or any header.
   */
  const PHASE_POST_RENDER = 3;

  /**
   * Post-send phase: no further alterations allowed.
   */
  const PHASE_POST_SEND = 4;

  /**
   * Add an email processor.
   *
   * Valid: initialisation.
   *
   * @param string $id
   *   A ID that can be used to alter or debug. Use a class or module name.
   * @param int $phase
   *   The phase to run in, one of the EmailInterface::PHASE_ constants.
   * @param callable
   *   The function to call.
   * @param int
   *   The weight, lower values run earlier.
   *
   * @return $this
   */
  public function addProcessor(string $id, int $phase, callable $function, int $weight = self::DEFAULT_WEIGHT);

  /**
   * Sets the langcode.
   *
   * Valid: build.
   *
   * @param string $langcode
   *   Language code to use to compose the email.
   *
   * @return $this
   */
  public function setLangcode(string $langcode);

  /**
   * Gets the langcode.
   *
   * @return string
   *   Language code to use to compose the email.
   */
  public function getLangcode();

  /**
   * Sets parameters used for building the email.
   *
   * Valid: initialisation.
   *
   * @param array $params
   *   (optional) An array of keyed objects or configuration.
   *
   * @return $this
   */
  public function setParams(array $params = []);

  /**
   * Adds a parameter used for building the email.
   *
   * If the value is an entity, then the key should be the entity type ID.
   * Otherwise the value is typically a setting that alters the email build.
   *
   * Valid: initialisation.
   *
   * @param string $key
   *   Parameter key to set.
   * @param $value
   *   Parameter value to set.
   *
   * @return $this
   */
  public function setParam(string $key, $value);

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
   *   Parameter value, or NULL if the parameter is not set.
   */
  public function getParam(string $key);

  /**
   * Sends the email.
   *
   * Valid: initialisation.
   *
   * @return bool
   *   Whether successful.
   */
  public function send();

  /**
   * Sets the account associated with the recipient of this email.
   *
   * Also sets the to address and langcode if they are not already set.
   *
   * Valid: build.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   (Optional) The account, which can be anonymous. If not set, then the
   *   account will be calculated from the to address.
   * @param bool $switch
   *   (Optional) Switch to this account for rendering.
   *
   * @return $this
   */
  public function setAccount(AccountInterface $account, bool $switch = FALSE);

  /**
   * Gets the account associated with the recipient of this email.
   *
   * @param bool $switch
   *   (Optional) Only return an account that needs switching to for rendering.
   *
   * @return \Drupal\Core\Session\AccountInterface
   *   The account.
   */
  public function getAccount(bool $switch = FALSE);

  /**
   * Sets the unrendered email body array.
   *
   * The email body will be rendered using a template, then used to form both
   * the HTML and plain text body parts. This process can be customised by the
   * email adjusters added to the email.
   *
   * Valid: before rendering.
   *
   * @param $body
   *   Unrendered email body array.
   *
   * @return $this
   */
  public function setBody($body);

  /**
   * Appends content to the email body array.
   *
   * Valid: before rendering.
   *
   * @param $body
   *   Unrendered body part to append to the existing body array.
   *
   * @return $this
   */
  public function appendBody($body);

  /**
   * Appends an entity to the email body array.
   *
   * WARNING: rendering as a trusted user can expose private information . Call
   * ::setAccount() unless you are sure this cannot occur.
   *
   * Valid: before rendering.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to render.
   * @param string $view_mode
   *   (optional) The view mode that should be used to render the entity.
   *
   * @return $this
   */
  public function appendBodyEntity(EntityInterface $entity, $view_mode = 'full');

  /**
   * Gets the unrendered email body array.
   *
   * Valid: before rendering.
   *
   * @return array
   *   Body render array.
   */
  public function getBody();

  /**
   * Sets variables available in the email template.
   *
   * Valid: build.
   *
   * @param array $variables
   *   An array of keyed variables.
   *
   * @return $this
   */
  public function setVariables(array $variables);

  /**
   * Sets a variable available in the email template.
   *
   * Valid: build.
   *
   * @param string $key
   *   Variable key to set.
   * @param $value
   *   Variable value to set.
   *
   * @return $this
   */
  public function setVariable(string $key, $value);

  /**
   * Gets variables available in the email template.
   *
   * @return array
   *   An array of keyed variables.
   */
  public function getVariables();

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
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface
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
   * Sets the email theme.
   *
   * Valid: build.
   *
   * @param string $theme_name
   *   The theme name to use for email.
   *
   * @return $this
   */
  public function setTheme(string $theme_name);

  /**
   * Gets the email theme name.
   *
   * @return string
   *   The email theme name.
   */
  public function getTheme();

  /**
   * Adds an asset library to use as mail CSS.
   *
   * Valid: before rendering.
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

  /**
   * Sets the mailer transport DSN to use.
   *
   * @param string $dsn
   *   Symfony mailer transport DSN.
   *
   * @return $this
   */
  public function setTransportDsn(string $dsn);

  /**
   * Gets the mailer transport DSN that will be used.
   *
   * @return string
   *   Transport DSN.
   */
  public function getTransportDSN();

}
