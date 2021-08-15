<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Html;
use Drupal\Core\Asset\AssetCollectionRendererInterface;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Utility\Token;
use Html2Text\Html2Text;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\NativeTransportFactory;
use Symfony\Component\Mailer\Transport\SendmailTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

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
   * The CSS asset collection renderer service.
   *
   * @var \Drupal\Core\Asset\AssetCollectionRendererInterface
   */
  protected $cssCollectionRenderer;

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The Symfony Mailer.
   *
   * @var \Symfony\Component\Mailer\MailerInterface
   */
  protected $mailer;

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
   */
  public function __construct(EventDispatcherInterface $dispatcher, RendererInterface $renderer, ModuleHandlerInterface $module_handler, Token $token, AssetResolverInterface $asset_resolver, AssetCollectionRendererInterface $css_collection_renderer, ConfigFactoryInterface $config_factory) {
    $this->dispatcher = $dispatcher;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
    $this->assetResolver = $asset_resolver;
    $this->cssCollectionRenderer = $css_collection_renderer;
    $this->configFactory = $config_factory;
    $this->createTransports();
    $this->mailer = new SymfonyMailer($this->transports['native'], NULL, $this->dispatcher);
  }

  /**
   * {@inheritdoc}
   */
  public function send(Email $message) {
    // Mailing can invoke rendering (e.g., generating URLs, replacing tokens),
    // but e-mails are not HTTP responses: they're not cached, they don't have
    // attachments. Therefore we perform mailing inside its own render context,
    // to ensure it doesn't leak into the render context for the HTTP response
    // to the current request.
    return $this->renderer->executeInRenderContext(new RenderContext(), function () use ($message) {
      return $this->doSend($message);
    });
  }

  /**
   * @internal
   */
  public function doSend(Email $message) {
    // Call hooks
    // @todo Also hook_email_KEY_alter().
    $this->moduleHandler->alter('email', $message);
    $message->getHeaders()->addTextHeader('X-Transport', 'smtp');

    // Set defaults.
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    $from = new Address($site_mail, $site_config->get('name'));

    if (empty($message->getFrom())) {
      $message->from($from);
    }
    if (empty($message->getSender())) {
      $message->sender($from);
    }
    if (empty($message->getReturnPath())) {
      $message->returnPath($from);
    }
    $message->getHeaders()->addTextHeader('X-Mailer', 'Drupal');

    // Render body.
    $content = $message->getContent();
    if (is_array($content)) {
      $content = $this->renderer->renderPlain($content);
    }

    // Replace tokens.
    $subject = $message->getSubject('subject');
    if ($message->requiresTokenReplace()) {
      if (!($subject instanceof MarkupInterface)) {
        $subject = Html::escape($subject);
      }
      $subject = $this->token->replace($subject, $message->getTokenData(), $message->getTokenOptions());
      $content = $this->token->replace($content, $message->getTokenData(), $message->getTokenOptions());
    }

    // Set subject.
    if ($subject instanceof MarkupInterface) {
      $subject = PlainTextOutput::renderFromHtml($subject);
    }
    $message->subject($subject);

    // Set body.
    $is_html = $message->isHtml();
    $this->setBody($message, $content, $is_html);
    if ($is_html && empty($message->getTextBody())) {
      // Set plain text alternative.
      $this->setBody($message, $content, FALSE);
    }

    // Send.
    $this->mailer->send($message);
  }

  protected function createTransports() {
    // @todo Read config and write to framework:mailer:transports:xxx
    // @see https://symfony.com/doc/current/mailer.html#multiple-email-transports
    $this->transports['native'] = (new NativeTransportFactory())->create(new Dsn('native', 'default'));
    $this->transports['sendmail'] = (new SendmailTransportFactory())->create(new Dsn('sendmail', 'default'));
  }

  /**
   * Sets the message body.
   *
   * @param \Drupal\symfony_mailer\Email $message
   *   The message to send.
   * @param boolean $is_html
   *   True if generating HTML output, false for plain text.
   *
   * @internal
   */
  protected function setBody(Email $message, $content, $is_html) {
    // @todo Read configured $text_format =
    // $text_format = $this->configFactory->get('symfony_mailer.settings')->get('text_format') ?: NULL;
    $text_format = NULL;

    if (!($content instanceof MarkupInterface)) {
      if ($is_html) {
        // Convert to HTML. The default 'plain_text' format escapes markup,
        // converts new lines to <br> and converts URLs to links.
        $content = check_markup($content, $text_format);
      }
      else {
        // The body will be plain text. However we need to convert to HTML
        // to render the template then convert back again. Use a fixed
        // conversion because we don't want to convert URLs to links.
        $content = preg_replace("|\n|", "<br />\n", HTML::escape($content)) . "<br />\n";
      }
    }

    // Convert relative URLs to absolute.
    $content = Markup::create(Html::transformRootRelativeUrlsToAbsolute((string) $content, \Drupal::request()->getSchemeAndHttpHost()));

    $render = [
      '#theme' => 'email',
      '#content' => $content,
      '#message' => $message,
      '#is_html' => $is_html,
    ];

    if ($is_html) {
      $assets = (new AttachedAssets())->setLibraries($message->getLibraries());
      // Request optimization so that the CssOptimizer performs essential
      // processing such as @include.
      $css_files = $this->assetResolver->getCssAssets($assets, TRUE);
      $css = $this->cssCollectionRenderer->render($css_files);
      $render['#css'] = $css;
    }

    $output = (string) $this->renderer->renderPlain($render);
    if ($is_html) {
      $message->html($output);
    }
    else {
      $message->text((new Html2Text($output))->getText());
    }
  }

}
