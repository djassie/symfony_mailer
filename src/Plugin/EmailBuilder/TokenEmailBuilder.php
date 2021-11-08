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
 *   weight = { "build" = 800, "adjust" = 200 }
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
    $this->options = $configuration['options'] ?? [];
    $this->options['callback'] = [$this, 'tokens'];
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
  public function build(UnrenderedEmailInterface $email) {
    $this->data = $configuration['data'] ?? $email->getParams();
    $this->data['variables'] = $email->getVariables();

    if (!empty($this->configuration['pre_render'])) {
      // Need to replace tokens before rendering so that the filters in the
      // text format can convert them to links.
      $body = $email->getBody();

      if ($body['#type'] == 'processed_text') {
        $body['#text'] = $this->token->replace($body['#text'], $this->data, $this->options);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function adjust(RenderedEmailInterface $email) {
    $this->data['variables']['body'] = $email->getHtmlBody();
    $inner = $email->getInner();
    $inner->subject(PlainTextOutput::renderFromHtml($this->token->replace(Html::escape($inner->getSubject()), $this->data, $this->options)));
    $email->setHtmlBody($this->token->replace($email->getHtmlBody(), $this->data, $this->options));
  }

  /**
   * Provides a callback for replacing tokens of type 'variable'.
   *
   * @param array $replacements
   *   An associative array variable containing mappings from token names to
   *   values (for use with strtr()).
   * @param array $data
   *   An array of keyed objects.
   * @param array $options
   *   A keyed array of settings and flags to control the token replacement
   *   process. See \Drupal\Core\Utility\Token::replace().
   *
   * @internal
   */
  public function tokens(array &$replacements, array $data, array $options) {
    if (!empty($data['variables'])) {
      foreach ($data['variables'] as $name => $value) {
        $replacements["[variables:$name]"] = $value;
      }
    }
  }

}
