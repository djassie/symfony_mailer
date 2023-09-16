<?php

namespace Drupal\Tests\symfony_mailer\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\symfony_mailer_test\MailerTestTrait;

/**
 * Tests basic email sending.
 *
 * @group filter
 */
class SymfonyMailerKernelTest extends KernelTestBase {

  use MailerTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['symfony_mailer', 'symfony_mailer_test', 'system', 'user', 'filter'];

  /**
   * The email factory.
   *
   * @var \Drupal\symfony_mailer\EmailFactoryInterface
   */
  protected $emailFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['symfony_mailer']);
    $this->installEntitySchema('user');
    $this->emailFactory = $this->container->get('email_factory');
    $this->config('system.site')
      ->set('name', 'Example')
      ->set('mail', 'sender@example.com')
      ->save();
  }

  /**
   * Basic email sending test.
   */
  public function testEmail() {
    // Test email error.
    $this->emailFactory->sendTypedEmail('symfony_mailer', 'test');
    $this->readMail();
    $this->assertError('An email must have a "To", "Cc", or "Bcc" header.');

    // Test email success.
    $to = 'to@example.com';
    $this->emailFactory->sendTypedEmail('symfony_mailer', 'test', $to);
    $this->readMail();
    $this->assertNoError();
    $this->assertSubject("Test email from Example");
    $this->assertTo($to);
  }

  /**
   * Inline CSS adjuster test.
   */
  public function testInlineCss() {
    $to = 'to@example.com';
    // Test an email including the test library.
    $this->emailFactory->newTypedEmail('symfony_mailer', 'test', $to)->addLibrary('symfony_mailer_test/inline_css_test')->send();
    $this->readMail();
    $this->assertNoError();
    // The inline CSS from inline.text-small.css should appear.
    $this->assertBodyContains('<h4 class="text-small" style="padding-top: 3px; padding-bottom: 3px; text-align: center; background-color: #0678be; color: white; font-size: smaller; font-weight: bold;">');
    // The imported CSS from inline.day.css should appear.
    $this->assertBodyContains('<span class="day" style="font-style: italic;">');
  }

}
