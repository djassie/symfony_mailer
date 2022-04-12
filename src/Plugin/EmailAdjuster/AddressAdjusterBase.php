<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Core\Form\FormStateInterface;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\MailerHelperTrait;
use Drupal\symfony_mailer\Processor\EmailAdjusterBase;
use Drupal\user\Entity\User;
use Symfony\Component\Mime\Address;

/**
 * Defines a base class for Email Adjusters that set an address field.
 */
abstract class AddressAdjusterBase extends EmailAdjusterBase {
  // @todo Setting whether to replace existing addresses or add to them.
  use MailerHelperTrait;

  /**
   * Sets the address in the appropriate header.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to process.
   * @param \Symfony\Component\Mime\Address[] $addresses
   *   The addresses to set.
   */
  abstract protected function setAddresses(EmailInterface $email, array $addresses);

  /**
   * {@inheritdoc}
   */
  public function postRender(EmailInterface $email) {
    foreach ($this->configuration['addresses'] as $item) {
      $value = $item['value'];
      $display = $item['display'];

      if ($value === '<site>') {
        $addresses[] = $this->helper()->getSiteAddress();
      }
      elseif ((strpos($value, '@') === FALSE) && ($user = User::load($value))) {
        $addresses[] = new Address($user->getEmail(), $user->getDisplayName());
      }
      else {
        $addresses[] = new Address($value, $display);
      }
    }

    $this->setAddresses($email, $addresses);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Set an id to allow updating the addresses when the type is changed.
    $id = $this->getPluginId();
    $wrapper = "mailer-policy-edit-$id";
    $form['addresses'] = [
      '#type' => 'container',
      '#attributes' => ['id' => $wrapper],
      '#element_validate' => [[static::class, 'addressesValidate']],
    ];

    // Synchronise with any existing form state.
    $addresses = $form_state->getValue(['config', $id, 'addresses']);
    if (!is_array($addresses)) {
      $addresses = $this->configuration['addresses'];
    }

    foreach ($addresses as $item) {
      $form_item['value'] = [
        '#type' => 'textfield',
        '#title' => t('Address'),
        '#default_value' => $item['value'] ?? NULL,
        '#description' => $this->t('Enter an email address, a user ID, or %site to use the site email address.', ['%site' => '<site>']),
      ];

      $form_item['display'] = [
        '#type' => 'textfield',
        '#title' => t('Display name'),
        '#default_value' => $item['display'] ?? NULL,
        '#description' => $this->t('Human-readable display name (ignored for user or site address).'),
      ];
      $form['addresses'][] = $form_item;
    }

    // Add address button.
    $form['add'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add address'),
      '#submit' => [[static::class, 'submitAdd']],
      '#ajax' => [
        'callback' => [static::class, 'ajaxUpdate'],
        'wrapper' => $wrapper,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    foreach ($this->configuration['addresses'] as $item) {
      $value = $item['value'];
      $display = $item['display'];

      if ($value === '<site>') {
        $summary[] = $this->t('Site email address');
      }
      elseif ((strpos($value, '@') === FALSE) && ($user = User::load($value))) {
        $summary[] = $user->getDisplayName();
      }
      else {
        $summary[] = $value;
      }
    }

    return implode(', ', $summary);
  }

  /**
   * Ajax callback to update the form.
   */
  public static function ajaxUpdate($form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $id = $button['#parents'][1];
    return $form['config'][$id]['addresses'];
  }

  /**
   * Submit callback for add button.
   */
  public static function submitAdd(array &$form, FormStateInterface $form_state) {
    $button = $form_state->getTriggeringElement();
    $id = $button['#parents'][1];
    $addresses = $form_state->getValue(['config', $id, 'addresses']);
    $addresses[] = [];
    $form_state->setValue(['config', $id, 'addresses'], $addresses)
      ->setRebuild();
  }

  /**
   * Validate callback for the addresses.
   */
  public static function addressesValidate($element, FormStateInterface $form_state, $form) {
    $id = $element['#parents'][1];
    $addresses = $form_state->getValue(['config', $id, 'addresses']);
    $addresses = array_filter($addresses, function ($a) {
      return !empty($a['value']);
    });
    $form_state->setValueForElement($element, $addresses);
  }

}
