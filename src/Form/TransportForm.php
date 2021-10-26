<?php

namespace Drupal\symfony_mailer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\symfony_mailer\TransportPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Mailer transport edit form.
 */
class TransportForm extends EntityForm {

  /**
   * The transport plugin being configured.
   *
   * @var \Drupal\symfony_mailer\TransportPluginInterface;
   */
  protected $plugin;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->plugin = $this->entity->getPlugin();
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $transport = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $transport->label(),
      '#description' => $this->t("Label for the Transport."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $transport->id(),
      '#machine_name' => [
        'exists' => '\Drupal\symfony_mailer\Entity\MailerTransport::load',
        'replace_pattern' => '[^a-z0-9_.]+',
        'source' => ['label'],
      ],
      '#required' => TRUE,
      '#disabled' => !$transport->isNew(),
    ];

    $form['plugin'] = [
      '#type' => 'value',
      '#value' => $transport->getPluginId(),
    ];

    $form += $this->plugin->buildConfigurationForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->plugin->validateConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->plugin->submitConfigurationForm($form, $form_state);
    $this->messenger()->addMessage($this->t('The transport configuration has been saved.'));
  }

}