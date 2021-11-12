<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Render\Markup;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Body Email Builder.
 *
 * @EmailBuilder(
 *   id = "email_body",
 *   label = @Translation("Body"),
 *   description = @Translation("Sets the email body."),
 * )
 */
class BodyEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $body = $this->configuration['value'];

    $variables = $email->getVariables();
    if ($existing_body = $email->getBody()) {
      $variables['body'] = $existing_body;
    }

    // There is little need for filtering because the output is an email, and
    // mail clients block dangerous content such as scripts. Furthermore, any
    // filtering, even Xss:filterAdmin(), will corrupt any tokens inside links
    // from the removal of 'unsafe protocols'.
    if ($variables) {
      // Apply TWIG template
      $body = [
        '#type' => 'inline_template',
        '#template' => $body,
        '#context' => $variables,
      ];
    }
    else {
      // Text is already markup, so ensure that it is not escaped again.
      $body = Markup::create($body);
    }

    $email->setBody($body);
  }

}
