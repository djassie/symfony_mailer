<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Form\FormStateInterface;

interface ConfigurableAdjusterInterface {

  /**
   * Generates an adjuster's settings form.
   *
   * @param array $form
   *   A minimally pre-populated form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the (entire) configuration form.
   *
   * @return array
   *   The $form array with additional form elements for the settings of this
   *   filter. The submitted form values should match $this->configuration.
   */
  public function settingsForm(array $form, FormStateInterface $form_state);

}
