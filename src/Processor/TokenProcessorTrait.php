<?php

namespace Drupal\symfony_mailer\Processor;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines a trait to enable token replacement in an Email processor.
 */
trait TokenProcessorTrait {

  protected array $data;
  protected array $options = [];

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    /** @var \Drupal\Core\Utility\Token $token */
    $token = \Drupal::token();
    $data = $this->data ?? $email->getParams();
    $inner = $email->getInner();

    if ($subject = $inner->getSubject()) {
      $subject = PlainTextOutput::renderFromHtml($token->replace(Html::escape($subject), $data, $this->options));
      $inner->subject($subject);
    }
    if ($body = $email->getHtmlBody()) {
      $email->setHtmlBody($token->replace($body, $data, $this->options));
    }
  }

  /**
   * Sets data for token replacement.
   *
   * @param array $data
   *   An array of keyed objects.
   */
  protected function tokenData(array $data) {
    $this->data = $data;
  }

  /**
   * Sets options for token replacement.
   *
   * @param array $options
   *   A keyed array of settings and flags.
   */
  protected function tokenOptions(array $options) {
    $this->options = $options;
  }

}
