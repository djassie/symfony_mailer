<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\symfony_mailer\Processor\EmailAdjusterManager;
use Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface;
use Symfony\Component\Mime\Address;

/**
 * Provides the mailer helper service.
 */
class MailerHelper implements MailerHelperInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The email adjuster manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailAdjusterManager
   */
  protected $adjusterManager;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface
   */
  protected $builderManager;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs the MailerHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\symfony_mailer\Processor\EmailAdjusterManager $email_adjuster_manager
   *   The email adjuster manager.
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManagerInterface $email_builder_manager
   *   The email builder manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EmailAdjusterManager $email_adjuster_manager, EmailBuilderManagerInterface $email_builder_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->adjusterManager = $email_adjuster_manager;
    $this->builderManager = $email_builder_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function parseAddress(string $encoded) {
    foreach (explode(',', $encoded) as $part) {
      $addresses[] = new Address($part);
    }
    return $addresses ?: [];
  }

  /**
   * {@inheritdoc}
   */
  public function policyFromAddresses(array $addresses) {
    $site_mail = $this->configFactory->get('system.site')->get('mail');

    foreach ($addresses as $address) {
      $value = $address->getAddress();
      if ($value == $site_mail) {
        $value = '<site>';
      }
      elseif ($user = user_load_by_mail($value)) {
        $value = $user->id();
      }
      else {
        $display = $address->getName();
      }

      // @todo Support multiple addresses.
      $config = [
        'value' => $value,
        'display' => $display ?? '',
      ];
    }

    return $config ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public function config() {
    return $this->configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function getSiteAddress() {
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    return new Address($site_mail, $site_config->get('name'));
  }

  /**
   * {@inheritdoc}
   */
  public function renderEntityPolicy(ConfigEntityInterface $entity, string $subtype) {
    $type = $entity->getEntityTypeId();
    $element = $this->renderCommon($type);
    $policy_id = "$type.$subtype";
    $entities = [$policy_id, $policy_id . '.' . $entity->id()];
    $element['listing'] = $this->entityTypeManager->getListBuilder('mailer_policy')
      ->overrideEntities($entities)
      ->hideColumns(['type', 'sub_type'])
      ->render();

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function renderTypePolicy(string $type) {
    $element = $this->renderCommon($type);
    $entities = [$type];
    foreach (array_keys($this->builderManager->getDefinition($type)['sub_types']) as $subtype) {
      $entities[] = "$type.$subtype";
    }

    $element['listing'] = $this->entityTypeManager->getListBuilder('mailer_policy')
      ->overrideEntities($entities)
      ->hideColumns(['type', 'entity'])
      ->render();

    return $element;
  }

  /**
   * Renders common parts for policy elements.
   *
   * @param string $type
   *   Type of the policies to show.
   *
   * @return array
   *   The render array.
   */
  protected function renderCommon(string $type) {
    $element = [
      '#type' => 'fieldset',
      '#title' => $this->t('Mailer policy'),
      '#collapsible' => FALSE,
      '#description' => $this->t('If you have made changes on this page, please save them before editing policy.'),
    ];

    $definition = $this->builderManager->getDefinition($type);
    $element['explanation'] = [
      '#prefix' => '<p>',
      '#markup' => $this->t('Configure Mailer policy records to customise the emails sent for @label.', ['@label' => $definition['label']]),
      '#suffix' => '</p>',
    ];

    foreach ($definition['common_adjusters'] as $adjuster_id) {
      $adjuster_names[] = $this->adjusterManager->getDefinition($adjuster_id)['label'];
    }

    if (!empty($adjuster_names)) {
      $element['explanation']['#markup'] .= ' ' . $this->t('You can set the @adjusters and more.', ['@adjusters' => implode(', ', $adjuster_names)]);
    }

    return $element;
  }

}
