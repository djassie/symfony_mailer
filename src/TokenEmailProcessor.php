<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\RenderedEmailInterface;

/**
 * Defines the Token replace Email processor.
 */
class TokenEmailProcessor implements EmailProcessorInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  protected array $data;
  protected array $options;

  /**
   * @param array $data
   *   (optional) An array of keyed objects.
   * @param array $options
   *   (optional) A keyed array of settings and flags.
   */
  public function __construct(?array $data = NULL, array $options = []) {
    if (!is_null($data)) {
      $this->data = $data;
    }
    $this->options = $options;
    $this->token = \Drupal::token();
  }

  /**
   * {@inheritdoc}
   */
  public function preRender(UnrenderedEmailInterface $email) {
  }

  /**
   * {@inheritdoc}
   */
  public function postRender(RenderedEmailInterface $email) {
    $data = $this->data ?? $email->getParams();
    $inner = $email->getInner();

    if ($subject = $inner->getSubject()) {
      $subject = PlainTextOutput::renderFromHtml($this->token->replace(Html::escape($subject), $data, $this->options));
      $inner->subject($subject);
    }
    if ($body = $email->getHtmlBody()) {
      $email->setHtmlBody($this->token->replace($body, $data, $this->options));
    }
  }

  public function getWeight(string $function) {
    return 600;
  }

}
