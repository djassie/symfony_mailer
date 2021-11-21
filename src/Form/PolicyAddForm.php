<?php

namespace Drupal\symfony_mailer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Mailer policy add form.
 */
class PolicyAddForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $emailBuilderManager = \Drupal::service('plugin.manager.email_builder');
    foreach ($emailBuilderManager->getDefinitions() as $id => $definition) {
      if (empty($definition['sub_type'])) {
        $types[$id] = $definition['label'];
      }
    }
    asort($types);

    // Set a div to allow updating the entire form when the type is changed.
    $form['#prefix'] = '<div id="mailer-policy-add-form">';
    $form['#suffix'] = '</div>';
    $ajax = [
      'callback' => '::ajaxUpdate',
      'wrapper' => 'mailer-policy-add-form',
      'effect' => 'fade',
    ];

    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#description' => $this->t("Email type that the policy applies to, or leave empty for all types."),
      '#options' => $types,
      '#empty_value' => '',
      '#empty_option' => $this->t('<b>*All*</b>'),
      '#ajax' => $ajax,
    ];

    // This form is Ajax enabled, so fetch the existing values if present.
    if ($type = $form_state->getValue('type')) {
      $definition = $emailBuilderManager->getDefinition($type);

      $form['sub_type'] = [
        '#title' => $this->t('Sub-type'),
        '#description' => $this->t("Email sub-type that the policy applies to, or leave empty for all sub-types."),
      ];

      if ($sub_types = $definition['sub_types']) {
        asort($sub_types);
        $form['sub_type'] += [
          '#type' => 'select',
          '#options' => $sub_types,
          '#empty_value' => '',
          '#empty_option' => $this->t('<b>*All*</b>'),
          '#ajax' => $ajax,
        ];
      }
      else {
        $form['sub_type']['#type'] = 'textfield';
      }

      if ($form_state->getValue('sub_type') && $definition['has_entity']) {
        $entities = [];
        foreach ($this->entityTypeManager->getStorage($type)->loadMultiple() as $id => $entity) {
          $entities[$id] = $entity->label();
        }
        asort($entities);

        $form['entity_id'] = [
          '#type' => 'select',
          '#title' => $this->t('Entity'),
          '#description' => $this->t("Entity that the policy applies to, or leave empty for all entities."),
          '#options' => $entities,
          '#empty_value' => '',
          '#empty_option' => $this->t('<b>*All*</b>'),
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo Check if the policy already exists (offer a link to it?)
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $id_array = [
      $form_state->getValue('type'),
      $form_state->getValue('sub_type'),
      $form_state->getValue('entity_id'),
    ];
    $id = implode('.', array_filter($id_array)) ?: '_';
    $form_state->setValue('id', $id)
      ->addCleanValueKey('type')
      ->addCleanValueKey('sub_type')
      ->addCleanValueKey('entity_id')
      ->setRedirect('entity.mailer_policy.edit_form', ['mailer_policy' => $id]);
    parent::submitForm($form, $form_state);
  }

  /**
   * Return the entire widget updated.
   */
  public function ajaxUpdate($form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Returns the action form element.
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $element = parent::actionsElement($form, $form_state);
    $element['submit']['#value'] = $this->t('Add and configure');
    return $element;
  }

}
