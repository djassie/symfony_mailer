<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Defines the interface for an Email that has not yet been rendered.
 *
 * Application code in a module that supports sending emails uses this
 * interface to build the email subject and unrendered body. Afterwards, the
 * Email is rendered, generating an object of type RenderedEmailInterface,
 * which has functions to configure other email headers and settings.
 *
 * @see \Drupal\symfony_mailer\UnrenderedEmailInterface
 */
interface UnrenderedEmailInterface extends BaseEmailInterface {

  /**
   * Sets the email subject.
   *
   * @param \Drupal\Component\Render\MarkupInterface|string $subject
   *   The email subject.
   *
   * @return $this
   */
  public function setSubject($subject);

  /**
   * Gets the email subject.
   *
   * @return \Drupal\Component\Render\MarkupInterface|string $subject
   *   The email subject.
   */
  public function getSubject();

  /**
   * Sets the unrendered email body.
   *
   * The email body will be rendered using a template, then used to form both
   * the HTML and plain text body parts. This process can be customised by the
   * email builders added to the email.
   *
   * @param $body
   *   Unrendered email body.
   *
   * @return $this
   */
  public function setBody($body);

  /**
   * Appends content to the email body.
   *
   * @param $body
   *   Unrendered body part to append to the existing body array.
   *
   * @return $this
   */
  public function appendBody($body);

  /**
   * Appends a rendered entity to the email body.
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
   * Gets the unrendered email body.
   *
   * @return array
   *   Body render array.
   */
  public function getBody();

  /**
   * Sets one or more to addresses.
   *
   * @param Address|string ...$addresses
   *
   * @return $this
   */
  public function setTo(...$addresses);

  /**
   * Gets the to addresses.
   *
   * @return array
   *   The to addresses.
   */
  public function getTo();

  /**
   * Sets one or more reply-to addresses.
   *
   * @param Address|string ...$addresses
   *
   * @return $this
   */
  public function setReplyTo(...$addresses);

  /**
   * Gets the reply-to addresses.
   *
   * @return array
   *   The reply-to addresses.
   */
  public function getReplyTo();

  /**
   * Add an email builder.
   *
   * @param string $plugin_id
   *   The ID of the email builder plugin.
   * @param array $configuration
   *   (Optional) Email builder configuration.
   * @param bool $optional
   *   (Optional) If TRUE, silently skip if the plugin doesn't exist.
   */
  public function addBuilder(string $plugin_id, array $configuration = []);

  /**
   * Sets the langcode.
   *
   * @param string $langcode
   *   Language code to use to compose the email.
   *
   * @return $this
   */
  public function setLangcode(string $langcode);

  /**
   * Sets parameters for hooks and to pass to the email template.
   *
   * @param array $params
   *   (optional) An array of keyed objects.
   *
   * @return $this
   */
  public function setParams(array $params = []);

  /**
   * Adds a parameter for hooks and to pass to the email template.
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
   * Sends the email.
   */
  public function send();

  /**
   * Renders the email.
   *
   * @internal
   *
   * @return RenderedEmailInterface
   *   Rendered email.
   */
  public function render();

}
