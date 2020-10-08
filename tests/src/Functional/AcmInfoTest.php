<?php

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
  protected static $modules = ['acm', 'acm_test'];

  /**
   * Test that information in info hooks implementation was collected correctly.
   */
  public function testInfoHooks() {
    $admin = $this->createUser([], NULL, TRUE);
    $this->drupalLogin($admin);

    $this->drupalGet('/admin/config/services/api_credentials_manager');

    $this->assertText('PROD');
    $this->assertText('UAT');

    $this->assertText('Service1');
    $this->assertText('Service2');

    $this->assertText('Provider1 API - UAT');
    $this->assertText('Provider1 API - PROD');
    $this->assertText('Provider2 - UAT');
    $this->assertText('Provider2 - PROD');
  }

}
