<?php

namespace Drupal\symfony_mailer\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\symfony_mailer\Entity\MailerPolicy;
use Drupal\symfony_mailer\MailerTransportInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Route controller for symfony mailer.
 */
class SymfonyMailerController extends ControllerBase {

  /**
   * Sets the transport as the default.
   *
   * @param \Drupal\symfony_mailer\Entity\MailerTransport $mailer_transport
   *   The mailer transport entity.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect to the transport listing page.
   */
  public function setAsDefault(MailerTransportInterface $mailer_transport) {
    $mailer_transport->setAsDefault();
    $this->messenger()->addStatus($this->t('The default transport is now %label.', ['%label' => $mailer_transport->label()]));
    return $this->redirect('entity.mailer_transport.collection');
  }

  /**
   * Creates a policy and redirects to the edit page.
   *
   * @param string $policy_id
   *   The policy ID.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect to the policy edit page.
   */
  public function createPolicy(string $policy_id, Request $request = NULL) {
    MailerPolicy::create(['id' => $policy_id])->save();
    $options = [];
    $query = $request->query;
    if ($query->has('destination')) {
      $options['query']['destination'] = $query->get('destination');
      $query->remove('destination');
    }
    return $this->redirect('entity.mailer_policy.edit_form', ['mailer_policy' => $policy_id], $options);
  }

}
