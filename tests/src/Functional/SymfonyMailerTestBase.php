<?php

namespace Drupal\Tests\symfony_mailer\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Base class for Symfony Mailer browser tests.
 */
abstract class SymfonyMailerTestBase extends BrowserTestBase {

  protected const TYPE_ALL = '<b>*All*</b>';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['symfony_mailer', 'symfony_mailer_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to manage mailer settings.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  protected $siteName = 'Tom & Jerry';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->config('system.site')->set('name', $this->siteName)->save();
    $this->adminUser = $this->drupalCreateUser(['administer mailer']);
  }

  protected function assertPolicyListingIntro(string $type, string $common) {
    $this->assertSession()->pageTextContains("Configure Mailer policy records to customise the emails sent for $type.");
    $this->assertSession()->pageTextContains("You can set the $common and more.");
  }

  protected function assertPolicyListingRow(string $row, string $sub_type, string $summary, string $id) {
    $base = "#edit-mailer-policy-listing-table tbody tr:nth-of-type($row)";
    $add = $summary ? '' : '/add';
    $this->assertSession()->elementContains('css', "$base td:nth-of-type(1)", $sub_type);
    $this->assertSession()->elementContains('css', "$base td:nth-of-type(2)", $summary);
    $this->assertSession()->elementAttributeContains('css', "$base td:nth-of-type(3) li a", 'href', "/admin/config/system/mailer/policy$add/$id");
  }

  /**
   * Gets the next email, removing it from the list.
   *
   * @return \Symfony\Component\Mime\Email
   *   The email.
   */
  protected function nextMail() {
    $emails = \Drupal::state()->get('mailer_test.emails', []);
    $email = array_shift($emails);
    \Drupal::state()->set('mailer_test.emails', $emails);
    return $email;
  }

  /**
   * Checks that the most recently sent email contains text.
   *
   * @param string $value
   *   Text to check for.
   */
  protected function assertBodyContains($value) {
    $captured_emails = $this->container->get('state')->get('system.test_mail_collector') ?: [];
    $email = end($captured_emails);
    $this->assertStringContainsString($value, (string) $email['body']);
  }

  /**
   * Checks the subject of the most recently sent email.
   *
   * @param string $value
   *   Text to check for.
   */
  protected function assertSubject($value) {
    $captured_emails = $this->container->get('state')->get('system.test_mail_collector') ?: [];
    $email = end($captured_emails);
    $this->assertEquals($value, (string) $email['subject']);
  }

  /**
   * Enables Plain text emails.
   */
  protected function enablePlain() {
    $this->config('swiftmailer.message')
      ->set('content_type', SWIFTMAILER_FORMAT_PLAIN)
      ->save();
  }

}
