<?php

namespace Drupal\symfony_mailer\Processor;

/**
 * Defines the interface for EmailBuilder plugins that support config import.
 */
interface MailerPolicyImportInterface {

  /**
   * Imports Mailer Policy from legacy email settings.
   */
  public function import();

}
