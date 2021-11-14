<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Component\Utility\Html;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;

/**
 * Defines the URL to absolute Email Builder.
 *
 * @EmailBuilder(
 *   id = "mailer_url_to_absolute",
 *   label = @Translation("URL to absolute"),
 *   description = @Translation("Convert URLs to absolute."),
 *   weight = 700,
 * )
 */
class AbsoluteUrlEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $email->setHtmlBody(Html::transformRootRelativeUrlsToAbsolute($email->getHtmlBody(), \Drupal::request()->getSchemeAndHttpHost()));
  }

}
