<?php

declare(strict_types = 1);

namespace Drupal\Tests\acm\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Info hook testing for ACM module.
 *
 * @group acm
 */
class AcmInfoTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['acm', 'acm_test'];

  /**
   * Test that information in info hooks implementation was collected correctly.
   */
  public function testInfoHooks(): void {
    $admin = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($admin);

    $this->drupalGet('/admin/config/services/api_credentials_manager');

    $this->assertSession()->pageTextContains('PROD');
    $this->assertSession()->pageTextContains('UAT');

    $this->assertSession()->pageTextContains('Service1');
    $this->assertSession()->pageTextContains('Service2');

    $this->assertSession()->pageTextContains('Provider1 API - UAT');
    $this->assertSession()->pageTextContains('Provider1 API - PROD');
    $this->assertSession()->pageTextContains('Provider2 - UAT');
    $this->assertSession()->pageTextContains('Provider2 - PROD');
  }

}
