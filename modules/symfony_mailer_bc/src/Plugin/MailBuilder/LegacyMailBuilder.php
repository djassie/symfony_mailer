<?php

namespace Drupal\symfony_mailer_bc\Plugin\MailBuilder;

use Drupal\Component\Render\MarkupInterface;
use Drupal\symfony_mailer\MailBuilderInterface;
use Drupal\symfony_mailer\Email;

/**
 * Defines the Legacy Mail Builder plug-in that calls hook_mail().
 *
 * @MailBuilder(
 *   id = "__legacy",
 *   label = @Translation("Legacy Mail Builder"),
 * )
 */
class LegacyMailBuilder implements MailBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function build(Email $email) {
    $message = $this->getMessage($email);
    $email->subject($message['subject']);

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

      $email->appendContent($content);
    }
  }

  /**
   * Gets a message array by calling hook_mail().
   *
   * @param \Drupal\symfony_mailer\Email $email
   *   The email to build.
   *
   * @return array
   *   Message array.
   */
  protected function getMessage($email) {
    list($module, $key) = $email->getKey();
    $message = [
      'id' => $module . '_' . $key,
      'module' => $module,
      'key' => $key,
      'to' => $email->getTo()[0],
      'from' => $email->getFrom()[0],
      'reply-to' => $email->getReplyTo()[0],
      'langcode' => $email->getLangcode(),
      'params' => $email->getParams(),
      'send' => TRUE,
      'subject' => '',
      'body' => [],
      'headers' => [],
    ];

    // Call hook_mail() on this module.
    if (function_exists($function = $module . '_mail')) {
      $function($key, $message, $email->getParams());
    }

    return $message;
  }

}
