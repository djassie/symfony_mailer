<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Html2Text\Html2Text;

/**
 * Defines the HTML to text Email Builder.
 *
 * @EmailBuilder(
 *   id = "mailer_html_to_text",
 *   label = @Translation("HTML to text"),
 *   description = @Translation("Create a plain text part from the HTML."),
 *   weight = 800,
 * )
 */
class HtmlToTextEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $inner = $email->getInner();

    if (!$inner->getTextBody()) {
      // @todo Or maybe use league/html-to-markdown as symfony mailer does.
      $inner->text((new Html2Text($email->getHtmlBody()))->getText());
    }
  }

}
