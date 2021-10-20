<?php

namespace Drupal\symfony_mailer;

use Drupal\Component\Utility\Html;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Utility\Token;
use Html2Text\Html2Text;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

/**
 * Provides a Mailer service based on Symfony Mailer.
 */
class EmailFactory {

  /**
   * The mailer service.
   *
   * @var \Drupal\symfony_mailer\MailerInterface
   */
  protected $mailer;

  /**
   * The email builder manager.
   *
   * @var \Drupal\symfony_mailer\EmailBuilderManager
   */
  protected $emailBuilderManager;

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
   * The CSS inliner.
   *
   * @var \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles
   */
  protected $cssInliner;

  /**
   * Constructs the Mailer object.
   *
   * @param Drupal\symfony_mailer\MailerInterface $mailer
   *   The mailer service.
   * @param Drupal\symfony_mailer\EmailBuilderManager
   *   The email builder manager.
   * @param \Drupal\Core\Utility\Token $token
   *   The token service.
   * @param \Drupal\Core\Asset\AssetResolverInterface $asset_resolver
   *   The asset resolver.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   */
  public function __construct(MailerInterface $mailer, EmailBuilderManager $email_builder_manager, Token $token, AssetResolverInterface $asset_resolver, ConfigFactoryInterface $config_factory) {
    $this->mailer = $mailer;
    $this->emailBuilderManager = $email_builder_manager;
    $this->token = $token;
    $this->assetResolver = $asset_resolver;
    $this->configFactory = $config_factory;
    $this->cssInliner = new CssToInlineStyles();
  }

  /**
   * {@inheritdoc}
   */
  public function newEmail($key) {
    $email = new Email($this->mailer, $key);

    foreach ($email->getKeySuggestions('', '.') as $id) {
      $this->addBuilder($email, $id);
    }

    $email->addAlter('post', [$this, 'defaultHeaders']);
    $email->addAlter('post', [$this, 'tokenReplace']);
    $email->addAlter('post', [$this, 'urlToAbsolute']);
    $email->addAlter('post', [$this, 'htmlToText']);
    $email->addAlter('post', [$this, 'inlineCss']);
    return $email;
  }

  /**
   * {@inheritdoc}
   */
  public function addBuilder(UnrenderedEmailInterface $email, string $id) {
    if ($this->emailBuilderManager->hasDefinition($id)) {
      $email->addAlter('pre', [$this->emailBuilderManager->createInstance($id), 'build']);
    }
  }

  /**
   * Sets default headers.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to alter.
   */
  public function defaultHeaders(RenderedEmailInterface $email) {
    $site_config = $this->configFactory->get('system.site');
    $site_mail = $site_config->get('mail') ?: ini_get('sendmail_from');
    $from = new Address($site_mail, $site_config->get('name'));
    $email->getInner()->from($from)
      ->sender($from)
      ->getHeaders()->addTextHeader('X-Mailer', 'Drupal');

    // @todo Fallback to default theme.
    $mail_theme = \Drupal::theme()->getActiveTheme()->getName();
    $email->addLibrary("$mail_theme/email");
  }

  /**
   * Replaces tokens.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to alter.
   */
  public function tokenReplace(RenderedEmailInterface $email) {
    $params = $email->getParams();
    $options = $params['token_options'] ?? NULL;
    if (isset($options)) {
      $inner = $email->getInner();
      $inner->subject($this->token->replace(Html::escape($inner->getSubject()), $params, $options));
      $email->setHtmlBody($this->token->replace($email->getHtmlBody(), $params, $options));
    }
  }

  /**
   * Converts URLs to absolute.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to alter.
   */
  public function urlToAbsolute(RenderedEmailInterface $email) {
    $email->setHtmlBody(Html::transformRootRelativeUrlsToAbsolute($email->getHtmlBody(), \Drupal::request()->getSchemeAndHttpHost()));
  }

  /**
   * Converts URLs to absolute.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to alter.
   */
  public function inlineCss(RenderedEmailInterface $email) {
    // Inline CSS. Request optimization so that the CssOptimizer performs
    // essential processing such as @include.
    $assets = (new AttachedAssets())->setLibraries($email->getLibraries());
    $css = '';
    foreach ($this->assetResolver->getCssAssets($assets, TRUE) as $file) {
      $css .= file_get_contents($file['data']);
    }

    if ($css) {
      $email->setHtmlBody($this->cssInliner->convert($email->getHtmlBody(), $css));
    }
  }

  /**
   * Creates a plain text part from the HTML.
   *
   * @param \Drupal\symfony_mailer\RenderedEmailInterface $email
   *   The email to alter.
   */
  public function htmlToText(RenderedEmailInterface $email) {
    $inner = $email->getInner();

    if (!$inner->getTextBody()) {
      // @todo Or maybe use league/html-to-markdown as symfony mailer does.
      $inner->text((new Html2Text($email->getHtmlBody()))->getText());
    }
  }

}
