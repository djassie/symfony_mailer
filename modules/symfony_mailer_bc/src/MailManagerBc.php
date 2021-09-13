<?php

namespace Drupal\symfony_mailer_bc;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\symfony_mailer\Email;
use Drupal\symfony_mailer\MailerInterface;

/**
 * Provides a back-compatibility shim for MailManager.
 */
class MailManagerBc extends DefaultPluginManager implements MailManagerInterface {

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
    parent::__construct('Plugin/symfony_mailer_bc', $namespaces, $module_handler, 'Drupal\symfony_mailer_bc\MailBcInterface', 'Drupal\symfony_mailer_bc\Annotation\MailBc');
    $this->setCacheBackend($cache_backend, 'symfony_mailer_bc_plugins');
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
    $email = (new Email("$module.$key"))->addTo($to);
    if ($reply) {
      $email->addReplyTo($reply);
    }

    // Retrieve the responsible implementation for this message.
    $this->getInstance(['module' => $module])->mail($email, $key, $to, $langcode, $params);
    $this->mailer->send($email);
  }

}
