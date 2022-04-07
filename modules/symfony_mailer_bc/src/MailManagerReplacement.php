<?php

namespace Drupal\symfony_mailer_bc;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Mail\MailManager;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\symfony_mailer\EmailFactory;
use Drupal\symfony_mailer\EmailInterface;
use Drupal\symfony_mailer\MailerHelperInterface;

/**
 * Provides a Symfony Mailer replacement for MailManager.
 */
class MailManagerReplacement extends MailManager {

  /**
   * List of headers for conversion to/from array.
   *
   * @var array
   */
  protected const HEADERS = [
    'From' => 'from',
    'Reply-To' => 'reply-to',
    'To' => 'to',
    'Cc' => 'cc',
    'Bcc' => 'bcc',
  ];

  /**
   * The email factory.
   *
   * @var \Drupal\symfony_mailer\EmailFactory
   */
  protected $emailFactory;

  /**
   * The mailer helper.
   *
   * @var \Drupal\symfony_mailer\MailerHelperInterface
   */
  protected $mailerHelper;

  /**
   * Constructs the MailManagerReplacement object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger channel factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\symfony_mailer\EmailFactory $email_factory;
   *   The email factory.
   * @param \Drupal\symfony_mailer\MailerHelperInterface $mailer_helper
   *   The mailer helper.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory, LoggerChannelFactoryInterface $logger_factory, TranslationInterface $string_translation, RendererInterface $renderer, EmailFactory $email_factory, MailerHelperInterface $mailer_helper) {
    parent::__construct($namespaces, $cache_backend, $module_handler, $config_factory, $logger_factory, $string_translation, $renderer);
    $this->emailFactory = $email_factory;
    $this->mailerHelper = $mailer_helper;
  }

  /**
   * {@inheritdoc}
   */
  public function mail($module, $key, $to, $langcode, $params = [], $reply = NULL, $send = TRUE) {
    $message = [
      'module' => $module,
      'key' => $key,
      'to' => $to,
      'langcode' => $langcode,
      'params' => $params,
      'reply-to' => $reply,
      'send' => $send,
      'subject' => '',
      'body' => [],
    ];
    $entity = NULL;

    // Call alter hooks.
    $this->moduleHandler->alter(['mailer_bc', "mailer_bc_$module"], $message, $entity);

    if ($entity) {
      $email = $this->emailFactory->newEntityEmail($entity, $message['key']);
    }
    else {
      $email = $this->emailFactory->newModuleEmail($message['module'], $message['key']);
    }

    $this->emailFromArray($email, $message);
    if ($message['send']) {
      $result = $email->send();
    }

    $message = $this->emailToArray($email);
    return $message + ['result' => $result ?? NULL, 'send' => $message['send']];
  }

  /**
   * Fills an Email from a message array.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to fill.
   * @param array $message
   *   The array to fill from.
   * @param array $original
   *   (Optional) The original message array.
   */
  public function emailFromArray(EmailInterface $email, array $message, array $original = []) {
    $email->setLangcode($message['langcode'])
      ->setParams($message['params'])
      ->setSubject($message['subject']);

    // Address headers.
    $headers = $email->getHeaders();
    foreach (self::HEADERS as $name => $key) {
      // If the header hasn't change then no need to parse it.
      $encoded = $message['headers'][$name] ?? $message[$key] ?? NULL;
      $encoded_original = $original['headers'][$name] ?? $original[$key] ?? NULL;
      if (isset($encoded) && ($encoded != $encoded_original)) {
        $addresses = $this->mailerHelper->parseAddress($encoded);
        if ($header = $headers->get($name)) {
          $header->setAddresses($addresses);
        }
        else {
          $headers->addMailboxListHeader($name, $addresses);
        }
      }
    }

    // Body.
    if (is_array($message['body'])) {
      foreach ($message['body'] as $part) {
        if ($part instanceof MarkupInterface) {
          $content = ['#markup' => $part];
        }
        else {
          $content = [
            '#type' => 'processed_text',
            '#text' => $part,
          ];
        }
        $email->appendBody($content);
      }
    }
    else {
      $email->setHtmlBody($message['body']);
    }
  }

  /**
   * Gets a message array for an Email.
   *
   * @param \Drupal\symfony_mailer\EmailInterface $email
   *   The email to convert.
   *
   * @return array
   *   Message array.
   */
  public function emailToArray(EmailInterface $email) {
    $module = $email->getType();
    $key = $email->getSubType();
    $message = [
      'id' => $module . '_' . $key,
      'module' => $module,
      'key' => $key,
      'langcode' => $email->getLangcode(),
      'params' => $email->getParams(),
      'send' => TRUE,
      'subject' => $email->getSubject(),
      'body' => $email->isRendered() ? $email->getHtmlBody() : $email->getBody(),
    ];

    // Address headers.
    $headers = $email->getHeaders();
    foreach (self::HEADERS as $name => $key) {
      if ($headers->has($name)) {
        $message['headers'][$name] = $headers->get($name)->getBodyAsString();
      }
      if ($key) {
        $message[$key] = $message['headers'][$name] ?? NULL;
      }
    }

    return $message;
  }

}
