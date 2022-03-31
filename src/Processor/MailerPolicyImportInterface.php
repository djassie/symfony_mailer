<?php

namespace Drupal\symfony_mailer\Processor;

interface MailerPolicyImportInterface {

  /**
   * Imports Mailer Policy from legacy email settings.
   */
  public function import();

}
