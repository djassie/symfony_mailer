<?php

namespace Drupal\symfony_mailer\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\ConfigurableAdjusterInterface;
use Drupal\symfony_mailer\Entity\MailerPolicy;

/**
 * Mailer policy edit form.
 */
class PolicyForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    foreach ($this->entity->adjusters() as $name => $adjuster) {
      if ($adjuster instanceof ConfigurableAdjusterInterface) {
        $form['config'][$name] = [
          '#type' => 'details',
          '#tree' => TRUE,
          '#title' => $adjuster->getLabel(),
          '#open' => TRUE,
          '#parents' => ['config', $name],
        ] + $adjuster->settingsForm([], $form_state);
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    // Add the submitted form values to the mailer policy, and save it.
    $policy = $this->entity;
    foreach ($form_state->getValues() as $key => $value) {
      foreach ($value as $plugin_id => $config) {
        $policy->setAdjusterConfig($plugin_id, $config);
      }
    }
    $policy->save();
  }

}
