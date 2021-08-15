<?php

namespace Drupal\symfony_mailer;

use Symfony\Component\Mime\Email as SymfonyEmail;

class Email extends SymfonyEmail {

  protected $key;
  protected $content;
  protected $isHtml = TRUE;
  protected $libraries = [];
  protected $tokenReplace = FALSE;
  protected $tokenData;
  protected $tokenOptions;

  public function __construct($key) {
    parent::__construct();
    $this->key = $key;
  }

  public function getKey() {
    return $this->key;
  }

  public function setContent($content) {
    $this->content = $content;
    return $this;
  }

  public function getContent() {
    return $this->content;
  }

  public function setHtml($is_html) {
    $this->isHtml = $is_html;
    return $this;
  }

  public function isHtml() {
    return $this->isHtml;
  }

  public function getLibraries() {
    return $this->libraries;
  }

  /**
   * Enables tokens replacement in the message subject and body.
   *
   * @param array $data
   *   (optional) An array of keyed objects.
   * @param array $options
   *   (optional) A keyed array of settings and flags to control the token
   *   replacement process.
   */
  public function enableTokenReplace(array $data = [], array $options = []) {
    $this->tokenReplace = TRUE;
    $this->tokenData = $data;
    $this->tokenOptions = $options;
    return $this;
  }

  public function requiresTokenReplace() {
    return $this->tokenReplace;
  }

  public function getTokenData() {
    return $this->tokenData;
  }

  public function getTokenOptions() {
    return $this->tokenOptions;
  }

}
