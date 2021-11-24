<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\symfony_mailer\EmailAdjusterBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Html2Text\Html2Text;

/**
 * Defines the HTML to text Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_html_to_text",
 *   label = @Translation("HTML to text"),
 *   description = @Translation("Create a plain text part from the HTML."),
 *   weight = 800,
 * )
 */
class HtmlToTextEmailAdjuster extends EmailAdjusterBase {

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    $inner = $email->getInner();

    if (!$inner->getTextBody()) {
      // @todo Or maybe use league/html-to-markdown as symfony mailer does.
      $inner->text((new Html2Text($email->getHtmlBody()))->getText());
    }
  }

}
