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
 * Defines the Subject header Email Builder.
 *
 * @EmailBuilder(
 *   id = "subject",
 *   label = @Translation("Subject"),
 *   description = @Translation("Sets the email subject."),
 * )
 */
class SubjectEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $subject = $this->configuration['value'];
    if ($token = $email->getBuilder('token_replace')) {
      // Replace tokens.
      $subject = $token->replacePlain($subject);
    }

    if ($variables = $email->getVariables()) {
      // Replace TWIG variables. @todo Use TWIG?
      $search = array_map(function($s) { return "{{ $s }}"; }, array_keys($variables));
      $subject = str_replace($search, array_values($variables), $subject);
    }

    $email->getInner()->subject($subject);
  }

}
