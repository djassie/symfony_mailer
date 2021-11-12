<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  public function adjust(RenderedEmailInterface $email) {
    $body = $this->configuration['value'];

    // 1) Replace tokens. This must be before TWIG so we don't replace tokens in
    // any original body.
    if ($token = $email->getBuilder('token_replace')) {
      $body = $token->replaceMarkup($body);
    }

    // 2) Apply TWIG template.
    if ($variables = $email->getVariables()) {
      $variables['body'] = Markup::create($email->getHtmlBody());

      $build = [
        '#type' => 'inline_template',
        '#template' => $body,
        '#context' => $variables,
      ];
      $this->renderer = \Drupal::service('renderer'); // @todo
      $body = $this->renderer->renderPlain($build);
    }

    // 3) Apply text format. This must be last so that it can act on any URLs
    // that have been replaced.
    $body = check_markup($body, $this->configuration['format']);
    $email->setHtmlBody($body);
  }

}
