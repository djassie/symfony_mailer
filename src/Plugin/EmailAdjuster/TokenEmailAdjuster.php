<?php

namespace Drupal\symfony_mailer\Plugin\EmailAdjuster;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\EmailProcessorBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Token replace Email Adjuster.
 *
 * @EmailAdjuster(
 *   id = "mailer_token_replace",
 *   label = @Translation("Token replace"),
 *   description = @Translation("Replace tokens in subject and body."),
 *   weight = 600
 * )
 */
class TokenEmailAdjuster extends EmailProcessorBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

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
  public function postRender(RenderedEmailInterface $email) {
    $data = $this->configuration['data'] ?? $email->getParams();
    $inner = $email->getInner();

    if ($subject = $inner->getSubject()) {
      $subject = PlainTextOutput::renderFromHtml($this->token->replace(Html::escape($subject), $data, $this->options));
      $inner->subject($subject);
    }
    if ($body = $email->getHtmlBody()) {
      $email->setHtmlBody($this->token->replace($body, $data, $this->options));
    }
  }

}
