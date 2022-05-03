<?php

namespace Drupal\Tests\symfony_mailer\Functional;

use Drupal\Component\Utility\Html;

/**
 * Test the test email.
 *
 * @group symfony_mailer
 */
class TestEmailTest extends SymfonyMailerTestBase {

  /**
   * Test sending a test email.
   */
  public function testTest() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/system/mailer/test');

    $this->assertPolicyListingIntro('Symfony Mailer', 'Subject, Body');
    $this->assertPolicyListingRow(1, self::TYPE_ALL, '', 'symfony_mailer');
    $this->assertPolicyListingRow(2, 'Test email', 'Body<br>Subject: Test email from [site:name]', 'symfony_mailer.test');

    $this->submitForm([], 'Send');
    $this->assertSession()->pageTextContains('An attempt has been made to send an email to you.');
    $email = $this->nextMail();
    $to = $email->getTo()[0];
    $this->assertEquals($this->adminUser->getEmail(), $to->getEmail());
    $this->assertEquals($this->adminUser->getDisplayName(), $to->getDisplayName());
    $this->assertEquals("Test email from $this->siteName", $email->getSubject());
    $escaped_site_name = Html::escape($this->siteName);
    $this->assertStringContainsString("This is a test email from <a href=\"$this->baseUrl/\">$escaped_site_name</a>.", $email->getHtmlBody());
  }

}
