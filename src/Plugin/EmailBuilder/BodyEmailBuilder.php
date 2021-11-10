<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Body Email Builder.
 *
 * @EmailBuilder(
 *   id = "body",
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
      $body = $token->replacePlain($body);
    }

    // 2) Replace TWIG variables. @todo Use TWIG?
    if ($variables = $email->getVariables()) {
      $variables['body'] = $email->getHtmlBody();
      $search = array_map(function($s) { return "{{ $s }}"; }, array_keys($variables));
      $body = str_replace($search, array_values($variables), $body);
    }

    // 3) Apply text format. This must be last so that it can act on any URLs
    // that have been replaced.
    $body = check_markup($body, $this->configuration['format']);
    $email->setHtmlBody($body);
  }

}
