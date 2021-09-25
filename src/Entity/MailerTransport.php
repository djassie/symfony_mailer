<?php

namespace Drupal\symfony_mailer\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Plugin\DefaultSingleLazyPluginCollection;
use Drupal\symfony_mailer\MailerTransportInterface;
use Symfony\Component\Mailer\Transport;

/**
 * Defines a Mailer Transport configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "mailer_transport",
 *   label = @Translation("Mailer Transport"),
 *   handlers = {
 *     "list_builder" = "Drupal\symfony_mailer\MailerTransportListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\symfony_mailer\Form\TransportForm",
 *       "add" = "Drupal\symfony_mailer\Form\TransportAddForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   admin_permission = "administer mailer",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/mailer/transport/{mailer_transport}",
 *     "delete-form" = "/admin/config/system/mailer/transport/{mailer_transport}/delete",
 *     "set-default" = "/admin/config/system/mailer/transport/{mailer_transport}/set-default",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "plugin",
 *     "configuration",
 *   }
 * )
 */
class MailerTransport extends ConfigEntityBase implements MailerTransportInterface, EntityWithPluginCollectionInterface {

  /**
   * The unique ID of the transport.
   *
   * @var string
   */
  protected $id = NULL;

  /**
   * The label of the transport.
   *
   * @var string
   */
  protected $label;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin instance configuration.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * The plugin collection that holds the plugin for this entity.
   *
   * @var \Drupal\Core\Plugin\DefaultSingleLazyPluginCollection
   */
  protected $pluginCollection;

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * Encapsulates the creation of the block's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The block's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $this->pluginCollection = new DefaultSingleLazyPluginCollection(\Drupal::service('plugin.manager.mailer_transport'), $this->plugin, $this->configuration);
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'configuration' => $this->getPluginCollection(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function setPluginId($plugin) {
    $this->plugin = $plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getDsn() {
    return $this->getPlugin()->getDsn();
  }

  /**
   * {@inheritdoc}
   */
  public function setAsDefault() {
    \Drupal::service('config.factory')->getEditable('mailer.settings')->set('default_transport', $this->id())->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isDefault() {
    return \Drupal::service('config.factory')->getEditable('mailer.settings')->get('default_transport') == $this->id();
  }

  /**
   * Gets the DSN for the specified transport.
   *
   * @param ?string $id
   *   The id of the transport to load.
   *
   * @return string
   *   The DSN.
   */
  public static function loadDsn(?string $id) {
    if ($id) {
      $transport_entity = static::load($id);
      if (!$transport_entity) {
        throw new \Exception("Mailer Transport $id not found");
      }
    }
    else {
      $transport_entity = static::loadDefault();
      if (!$transport_entity) {
        throw new \Exception("No default Mailer Transport found");
      }
    }

    return Transport::fromDsn($transport_entity->getDsn());
  }

  /**
   * Gets the default transport.
   *
   * @return \Drupal\symfony_mailer\MailerTransportInterface
   *   The default transport.
   */
  public static function loadDefault() {
    $id = \Drupal::config('mailer.settings')->get('default_transport');
    return $id ? static::load($id) : NULL;
  }

}
