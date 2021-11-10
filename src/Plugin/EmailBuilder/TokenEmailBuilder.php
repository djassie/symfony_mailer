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
 * Defines the Token replace Email Builder.
 *
 * @EmailBuilder(
 *   id = "token_replace",
 *   label = @Translation("Token replace"),
 *   description = @Translation("Replace tokens in subject and body."),
 *   weight = 600
 * )
 */
class TokenEmailBuilder extends EmailBuilderBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  protected array $data;
  protected array $options;

  /**
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Token $token) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->token = $token;
    $this->data = $configuration['data'] ?? $configuration['email']->getParams();
    $this->options = $configuration['options'] ?? [];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $inner = $email->getInner();
    if ($subject = $inner->getSubject()) {
      $inner->subject($this->replacePlain($subject));
    }
    if ($body = $email->getHtmlBody()) {
      $email->setHtmlBody($this->replaceMarkup($body));
    }
  }

  /**
   * Replaces tokens in a plain-text string.
   *
   * @param string $plain
   *   The plain-text string.
   *
   * @return string
   *   The plain-text result.
   */
  public function replacePlain(string $plain) {
    return PlainTextOutput::renderFromHtml($this->token->replace(Html::escape($plain), $this->data, $this->options));
  }

  /**
   * Replaces tokens in an HTML markup string.
   *
   * @param string $markup
   *   The markup string.
   *
   * @return string
   *   The markup result.
   */
  public function replaceMarkup(string $markup) {
    return $this->token->replace($markup, $this->data, $this->options);
  }

}
