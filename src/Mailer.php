<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageDefault;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\symfony_mailer\Entity\MailerTransport;
use Symfony\Component\Mailer\Exception\RuntimeException;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
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
   * The language default.
   *
   * @var \Drupal\Core\Language\LanguageDefault
   */
  protected $languageDefault;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs the Mailer object.
   *
   * @param \Symfony\Contracts\EventDispatcher\EventDispatcherInterface $dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Language\LanguageDefault $default_language
   *   The default language.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EventDispatcherInterface $dispatcher, RendererInterface $renderer, ModuleHandlerInterface $module_handler, LanguageDefault $language_default, LanguageManagerInterface $language_manager) {
    $this->dispatcher = $dispatcher;
    $this->renderer = $renderer;
    $this->moduleHandler = $module_handler;
    $this->languageDefault = $language_default;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function send(UnrenderedEmailInterface $email) {
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
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email to send.
   *
   * @return bool
   *   Whether successful.
   *
   * @internal
   */
  public function doSend(UnrenderedEmailInterface $email) {
    $langcode = $email->getLangcode();
    $currentLangcode = $this->languageManager->getCurrentLanguage()->getId();
    $mustSwitch = isset($langcode) && $langcode !== $currentLangcode;

    if ($mustSwitch) {
      $this->changeActiveLanguage($langcode);
    }

    // Call alter hooks.
    $this->moduleHandler->alter($email->getKeySuggestions('email', '_'), $email);

    // Call pre-render hooks.
    $this->alter('pre', $email);

    // Render.
    /** @var \Drupal\symfony_mailer\RenderedEmailInterface $rendered_email */
    $rendered_email = $email->render($this->renderer);

    // Call post-render hooks.
    $this->alter('post', $rendered_email);

    // Send.
    $dsn = MailerTransport::loadDsn($rendered_email->getTransport());
    $mailer = new SymfonyMailer($dsn, NULL, $this->dispatcher);

    try {
      //ksm($rendered_email, $rendered_email->getInner()->getHeaders());
      $mailer->send($rendered_email->getInner());
      $result = TRUE;
    }
    catch (RuntimeException $e) {
      // @todo Log exception, print user-focused message.
      \Drupal::messenger()->addWarning($e->getMessage());
      $result = FALSE;
    }

    if ($mustSwitch) {
      $this->changeActiveLanguage($currentLangcode);
    }

    return $result;
  }

  /**
   * Changes the active language for translations.
   *
   * @param string $langcode
   *   The langcode.
   */
  protected function changeActiveLanguage($langcode) {
    // Language switching adapted from commerce module.
    // @see \Drupal\commerce\MailHandler::sendMail
    if (!$this->languageManager->isMultilingual()) {
      return;
    }

    $language = $this->languageManager->getLanguage($langcode);
    if (!$language) {
      return;
    }
    // The language manager has no method for overriding the default language,
    // like it does for config overrides. We have to change the default
    // language service's current language.
    // @see https://www.drupal.org/project/drupal/issues/3029010
    $this->languageDefault->set($language);
    $this->languageManager->setConfigOverrideLanguage($language);
    $this->languageManager->reset();

    // The default string_translation service, TranslationManager, has a
    // setDefaultLangcode method. However, this method is not present on either
    // of its interfaces. Therefore we check for the concrete class here so
    // that any swapped service does not break the application.
    // @see https://www.drupal.org/project/drupal/issues/3029003
    $string_translation = $this->getStringTranslation();
    if ($string_translation instanceof TranslationManager) {
      $string_translation->setDefaultLangcode($language->getId());
      $string_translation->reset();
    }
  }

  /**
   * Calls the alter functions.
   *
   * @param string $type
   *   The callback type: pre or post.
   * @param \Drupal\symfony_mailer\BaseEmailInterface $email
   *   The email to alter.
   */
  protected function alter($type, $email) {
    foreach ($email->getAlter($type) as $callback) {
      $callback($email);
    }
  }

}
