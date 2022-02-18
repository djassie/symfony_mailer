<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\Processor\EmailAdjusterManager;
use Drupal\symfony_mailer\Processor\EmailBuilderManager;

/**
 * Provides the mailer helper service.
 */
class MailerHelper implements MailerHelperInterface {

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
   * @var \Drupal\symfony_mailer\Processor\EmailBuilderManager
   */
  protected $builderManager;

  /**
   * Constructs the MailerHelper object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\symfony_mailer\Processor\EmailAdjusterManager $email_adjuster_manager
   *   The email adjuster manager.
   * @param \Drupal\symfony_mailer\Processor\EmailBuilderManager $email_builder_manager
   *   The email builder manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EmailAdjusterManager $email_adjuster_manager, EmailBuilderManager $email_builder_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->adjusterManager = $email_adjuster_manager;
    $this->builderManager = $email_builder_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function renderEntityPolicy(ConfigEntityInterface $entity, string $subtype, array $common_adjusters = ['email_subject', 'email_from']) {
    $type = $entity->getEntityTypeId();
    $element = $this->renderCommon($type, $common_adjusters);
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
  public function renderTypePolicy(string $type, array $common_adjusters = ['email_subject', 'email_from']) {
    $element = $this->renderCommon($type, $common_adjusters);
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
   * @param string[] $common_adjusters
   *   ID of EmailAdjusters to use as an example in the description.
   *
   * @return array
   *   The render array.
   */
  protected function renderCommon(string $type, array $common_adjusters) {
    $element = [
      '#type' => 'fieldset',
      '#title' => t('Mailer policy'),
      '#collapsible' => FALSE,
      '#description' => t('If you have made changes on this page, please save them before editing policy.'),
    ];

    foreach ($common_adjusters as $adjuster_id) {
      $adjuster_names[] = $this->adjusterManager->getDefinition($adjuster_id)['label'];
    }
    $label = $this->builderManager->getDefinition($type)['label'];

    $element['explanation'] = [
      '#prefix' => '<p>',
      '#markup' => t('Configure Mailer policy records to customise the emails sent for @label. You can set the @adjusters and more.', ['@label' => $label, '@adjusters' => implode(', ', $adjuster_names)]),
      '#suffix' => '</p>',
    ];

    return $element;
  }

}