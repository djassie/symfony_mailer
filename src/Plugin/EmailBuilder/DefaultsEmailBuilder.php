<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Mime\Address;

/**
 * Defines the Default headers Email Builder.
 *
 * @EmailBuilder(
 *   id = "mailer_default_headers",
 *   label = @Translation("Default headers"),
 *   description = @Translation("Set default headers."),
 *   weight = 100,
 * )
 */
class DefaultsEmailBuilder extends EmailBuilderBase implements ContainerFactoryPluginInterface {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   The theme manager.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *   The library discovery service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, ThemeManagerInterface $theme_manager, LibraryDiscoveryInterface $library_discovery) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $config_factory;
    $this->themeManager = $theme_manager;
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('theme.manager'),
      $container->get('library.discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    $from = new Address($site_mail, $site_config->get('name'));
    $email->getInner()->sender($from)
      ->getHeaders()->addTextHeader('X-Mailer', 'Drupal');

    // First try active theme then fallback to default theme.
    $theme = $this->themeManager->getActiveTheme()->getName();
    if (!$this->libraryDiscovery->getLibraryByName($theme, 'email')) {
      $theme = $this->configFactory->get('system.theme')->get('default');
    }
    $email->addLibrary("$theme/email");
  }

}
