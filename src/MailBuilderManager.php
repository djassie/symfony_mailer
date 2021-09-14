<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Mail\MailManagerInterface;

/**
 * Provides a Symfony Mailer replacement for MailManager.
 */
class MailBuilderManager extends DefaultPluginManager implements MailManagerInterface {

  /**
   * The mailer.
   *
   * @var Drupal\symfony_mailer\MailerInterface
   */
  protected $mailer;

  /**
   * Constructs the MailManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, MailerInterface $mailer) {
    parent::__construct('Plugin/MailBuilder', $namespaces, $module_handler, 'Drupal\symfony_mailer\MailBuilderInterface', 'Drupal\symfony_mailer\Annotation\MailBuilder');
    $this->setCacheBackend($cache_backend, 'symfony_mailer_builder_plugins');
    $this->mailer = $mailer;
  }

  /**
   * {@inheritdoc}
   */
  public function getInstance(array $options) {
    if (isset($options['module'])) {
      return $this->createInstance($options['module']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function mail($module, $key, $to, $langcode, $params = [], $reply = NULL, $send = TRUE) {
    $email = $this->mailer->newEmail([$module, $key])
      ->addTo($to)
      ->langcode($langcode)
      ->params($params);
    if ($reply) {
      $email->addReplyTo($reply);
    }

    // Retrieve the responsible implementation for this message.
    $this->getInstance(['module' => $module])->mail($email, $key, $to, $langcode, $params);
    $email->send();
  }

}
