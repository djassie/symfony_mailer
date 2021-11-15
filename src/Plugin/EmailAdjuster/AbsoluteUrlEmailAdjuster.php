<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Component\Utility\Html;
use Drupal\symfony_mailer\EmailProcessorBase;
use Drupal\symfony_mailer\RenderedEmailInterface;

/**
 * Defines the URL to absolute Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_url_to_absolute",
 *   label = @Translation("URL to absolute"),
 *   description = @Translation("Convert URLs to absolute."),
 *   weight = 700,
 * )
 */
class AbsoluteUrlEmailAdjuster extends EmailProcessorBase {

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    $email->setHtmlBody(Html::transformRootRelativeUrlsToAbsolute($email->getHtmlBody(), \Drupal::request()->getSchemeAndHttpHost()));
  }

}
