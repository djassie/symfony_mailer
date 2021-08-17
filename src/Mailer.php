<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Utility\Html;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
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
   */
  public function __construct(EventDispatcherInterface $dispatcher, RendererInterface $renderer, ModuleHandlerInterface $module_handler, Token $token, AssetResolverInterface $asset_resolver, ConfigFactoryInterface $config_factory) {
    $this->dispatcher = $dispatcher;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->token = $token;
    $this->assetResolver = $asset_resolver;
    $this->configFactory = $config_factory;
    // @todo Maybe should override the options to pass -bs.
    // @see https://swiftmailer.symfony.com/docs/sending.html
    $this->defaultTransport = Transport::fromDsn('native://default');
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

    if (!$message->getLibraries()) {
      // @todo Configure mail theme.
      $mail_theme = \Drupal::theme()->getActiveTheme()->getName();
      $message->addLibrary("$mail_theme/email");
    }
    if (!$message->getTransport()) {
      $message->setTransport($this->defaultTransport);
    }

    // Call hooks
    // @todo Also hook_email_KEY_alter().
    $this->moduleHandler->alter('email', $message);

    // Set body.
    $is_html = $message->isHtml();
    $this->setBody($message, $is_html);
    // @todo Allow caller to supply a custom plain text alternative.
    if ($is_html) {
      // Set plain text alternative.
      $this->setBody($message, FALSE);
    }

    // Send.
    $mailer = new SymfonyMailer($message->getTransport(), NULL, $this->dispatcher);
    try {
      ksm($message);
      $mailer->send($message);
    }
    catch (RuntimeException $e) {
      \Drupal::messenger()->addWarning($e->getMessage());
    }
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
  protected function setBody(Email $message, $is_html) {
    $render = [
      '#theme' => 'email',
      '#message' => $message,
      '#is_html' => $is_html,
    ];

    if ($is_html) {
      $assets = (new AttachedAssets())->setLibraries($message->getLibraries());
      // Request optimization so that the CssOptimizer performs essential
      // processing such as @include.
      $css_files = $this->assetResolver->getCssAssets($assets, TRUE);
      $css = '';
      foreach ($css_files as $file) {
        $css .= file_get_contents($file['data']);
      }
      $render['#css'] = $css;
    }

    $output = (string) $this->renderer->renderPlain($render);
    $message->sending();

    // Replace tokens.
    if ($message->requiresTokenReplace()) {
      $message->subject($this->token->replace(Html::escape($message->getSubject()), $message->getData(), $message->getTokenOptions()));
      $output = $this->token->replace($output, $message->getData(), $message->getTokenOptions());
    }

    // Convert relative URLs to absolute.
    $output = Html::transformRootRelativeUrlsToAbsolute($output, \Drupal::request()->getSchemeAndHttpHost());

    if ($is_html) {
      $message->html($output);
    }
    else {
      // @todo Or maybe use league/html-to-markdown?
      $message->text((new Html2Text($output))->getText());
    }
  }

}
