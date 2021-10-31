<?php

namespace Drupal\symfony_mailer\Plugin\EmailBuilder;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\RenderedEmailInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines the Token replace Email Builder.
 *
 * @EmailBuilder(
 *   id = "token_replace",
 *   label = @Translation("Token replace"),
 *   description = @Translation("Replace tokens in subject and body."),
 *   weight = 200,
 * )
 */
class TokenEmailBuilder extends EmailBuilderBase implements ContainerFactoryPluginInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

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
    $options = $this->configuration['options'] ?? [];
    $data = $this->configuration['data'] ?? $email->getParams();
    $inner = $email->getInner();
    $inner->subject(PlainTextOutput::renderFromHtml($this->token->replace(Html::escape($inner->getSubject()), $data, $options)));
    $email->setHtmlBody($this->token->replace($email->getHtmlBody(), $data, $options));
  }

}
