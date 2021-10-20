<?php

namespace Drupal\symfony_mailer_bc\Plugin\EmailBuilder;

use Drupal\Component\Render\MarkupInterface;
use Drupal\symfony_mailer\EmailBuilderBase;
use Drupal\symfony_mailer\UnrenderedEmailInterface;

/**
 * Defines the Legacy Email Builder plug-in that calls hook_mail().
 *
 * @EmailBuilder(
 *   id = "__legacy",
 *   label = @Translation("Legacy Email Builder"),
 * )
 */
class LegacyEmailBuilder extends EmailBuilderBase {

  /**
   * {@inheritdoc}
   */
  public function build(UnrenderedEmailInterface $email) {
    $message = $this->getMessage($email);
    $email->setSubject($message['subject']);

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

  /**
   * Gets a message array by calling hook_mail().
   *
   * @param \Drupal\symfony_mailer\UnrenderedEmailInterface $email
   *   The email to build.
   *
   * @return array
   *   Message array.
   */
  protected function getMessage(UnrenderedEmailInterface $email) {
    list($module, $key) = $email->getKey();
    $message = [
      'id' => $module . '_' . $key,
      'module' => $module,
      'key' => $key,
      'to' => $email->getTo()[0],
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
