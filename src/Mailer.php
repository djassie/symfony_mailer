<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Utility\Token;
use Html2Text\Html2Text;
use Symfony\Component\Mailer\Exception\RuntimeException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Provides a Mailer service based on Symfony Mailer.
 */
class Mailer implements MailerInterface {

  /**
   * The event dispatcher to notify of routes.
   *
   * @var \Symfony\Contracts\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module handler to invoke the alter hook.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * The asset resolver.
   *
   * @var \Drupal\Core\Asset\AssetResolverInterface
   */
  protected $assetResolver;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  protected $defaultTransport;

  /**
   * Constructs the Mailer object.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   * @param \Drupal\Core\Asset\AssetCollectionRendererInterface $css_collection_renderer
   *   The CSS asset collection renderer.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EventDispatcherInterface $dispatcher, RendererInterface $renderer, ModuleHandlerInterface $module_handler, Token $token, AssetResolverInterface $asset_resolver, ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager) {
    $this->dispatcher = $dispatcher;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
    $this->assetResolver = $asset_resolver;
    $this->configFactory = $config_factory;
    $this->languageManager = $language_manager;
    // @todo Maybe should override the options to pass -bs.
    // @see https://swiftmailer.symfony.com/docs/sending.html
    $this->defaultTransport = Transport::fromDsn('native://default');
  }

  /**
   * {@inheritdoc}
   */
  public function newEmail($key) {
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    $from = new Address($site_mail, $site_config->get('name'));
    return new Email($this, $key, $from);
  }

  /**
   * {@inheritdoc}
   */
  public function send(Email $email) {
    // Mailing can invoke rendering (e.g., generating URLs, replacing tokens),
    // but e-mails are not HTTP responses: they're not cached, they don't have
    // attachments. Therefore we perform mailing inside its own render context,
    // to ensure it doesn't leak into the render context for the HTTP response
    // to the current request.
    return $this->renderer->executeInRenderContext(new RenderContext(), function () use ($email) {
      return $this->doSend($email);
    });
  }

  /**
   * Sends an email.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to send.
   *
   * @return bool
   *   Whether successful.
   *
   * @internal
   */
  public function doSend(Email $email) {
    if ($langcode = $email->getLangcode()) {
      // @todo This only switches language for config. We also want to switch
      // all translations, tokens, url, etc.
      // @see https://drupal.stackexchange.com/questions/156094/switch-a-language-programatically
      $original_language = $this->languageManager->getConfigOverrideLanguage();
      $this->languageManager->setConfigOverrideLanguage($this->languageManager->getLanguage($langcode));
    }

    if (!$email->getLibraries()) {
      // @todo Configure mail theme.
      $mail_theme = \Drupal::theme()->getActiveTheme()->getName();
      $email->addLibrary("$mail_theme/email");
    }
    if (!$email->getTransport()) {
      $email->transport($this->defaultTransport);
    }

    // Call hooks
    $this->moduleHandler->alter($email->getKeySuggestions('email', '_'), $email);

    // Set body.
    $this->setBody($email, $email->isHtml());

    // Send.
    $mailer = new SymfonyMailer($email->getTransport(), NULL, $this->dispatcher);
    try {
      //ksm($email, $email->getHeaders());
      $mailer->send($email);
      $result = TRUE;
    }
    catch (RuntimeException $e) {
      // @todo Log exception, print user-focused message.
      \Drupal::messenger()->addWarning($e->getMessage());
      $result = FALSE;
    }

    if (isset($original_language)) {
      $this->languageManager->setConfigOverrideLanguage($original_language);
    }

    return $result;
  }

  /**
   * Sets the message body.
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to send.
   * @param boolean $is_html
   *   True if generating HTML output, false for plain text.
   *
   * @internal
   */
  protected function setBody(Email $email, $is_html) {
    $render = [
      '#theme' => 'email',
      '#email' => $email,
      '#is_html' => $is_html,
    ];

    $output = (string) $this->renderer->renderPlain($render);
    $email->sending();

    // Replace tokens.
    if ($email->requiresTokenReplace()) {
      $email->subject($this->token->replace(Html::escape($email->getSubject()), $email->getParams(), $email->getTokenOptions()));
      $output = $this->token->replace($output, $email->getParams(), $email->getTokenOptions());
    }

    // Convert relative URLs to absolute.
    $output = Html::transformRootRelativeUrlsToAbsolute($output, \Drupal::request()->getSchemeAndHttpHost());

    if ($is_html) {
      // Inline CSS. Request optimization so that the CssOptimizer performs
      // essential processing such as @include.
      $assets = (new AttachedAssets())->setLibraries($email->getLibraries());
      $css = '';
      foreach ($this->assetResolver->getCssAssets($assets, TRUE) as $file) {
        $css .= file_get_contents($file['data']);
      }

      $html_output = $css ? (new CssToInlineStyles())->convert($output, $css) : $output;
      $email->html($html_output);
    }

    // Text body or plain-text alternative.
    if (!$is_html || !$email->getTextBody()) {
      // @todo Or maybe use league/html-to-markdown as symfony mailer does?
      $email->text((new Html2Text($output))->getText());
    }
  }

}
