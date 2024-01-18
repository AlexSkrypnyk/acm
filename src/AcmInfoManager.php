<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Core\Extension\ModuleHandler;

/**
 * Class AcmInfoManager.
 *
 * Collects information provided by info hook implementations.
 *
 * @package Drupal\acm
 */
class AcmInfoManager {

  /**
   * Array of environments.
   *
   * @var \Drupal\acm\AcmEnvironment[]
   */
  protected array $environments;

  /**
   * Array of endpoints.
   *
   * @var \Drupal\acm\AcmEndpoint[]
   */
  protected array $endpoints;

  /**
   * Array of credential definitions.
   *
   * @var \Drupal\acm\AcmCredential[]
   */
  protected array $credentials;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * AcmInfoManager constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler. Used to invoke hooks.
   */
  public function __construct(ModuleHandler $module_handler) {
    $this->moduleHandler = $module_handler;

    $this->environments = $this->collectEnvironments();
    $this->endpoints = $this->collectEndpoints();
    $this->credentials = $this->collectCredentials();
  }

  /**
   * Get environments.
   *
   * @return \Drupal\acm\AcmEnvironment[]
   *   Array of environment instances.
   */
  public function getEnvironments(): array {
    return $this->environments;
  }

  /**
   * Get endpoints.
   *
   * @return \Drupal\acm\AcmEndpoint[]
   *   Array of endpint instances.
   */
  public function getEndpoints(): array {
    return $this->endpoints;
  }

  /**
   * Get credential definitions.
   *
   * @return \Drupal\acm\AcmCredential[]
   *   Array of credential definition instances.
   */
  public function getCredentials(): array {
    return $this->credentials;
  }

  /**
   * Collect environments from info hooks and instantiate entities.
   *
   * @return \Drupal\acm\AcmEnvironment[]
   *   Array of environment definition instances.
   */
  protected function collectEnvironments(): array {
    /** @var \Drupal\acm\AcmEnvironment[] $environments */
    $environments = $this->createInstances('acm_environments_info', 'Drupal\acm\AcmEnvironment');

    return $environments;
  }

  /**
   * Collect endpoints from info hooks and instantiate entities.
   *
   * @return \Drupal\acm\AcmEndpoint[]
   *   Array of endpoint definition instances.
   */
  protected function collectEndpoints(): array {
    /** @var \Drupal\acm\AcmEndpoint[] $endpoints */
    $endpoints = $this->createInstances('acm_endpoints_info', 'Drupal\acm\AcmEndpoint');

    return $endpoints;
  }

  /**
   * Collect credential definitions from info hooks and instantiate entities.
   *
   * @return \Drupal\acm\AcmCredential[]
   *   Array of credential definition instances.
   */
  protected function collectCredentials(): array {
    /** @var \Drupal\acm\AcmCredential[] $credentials */
    $credentials = $this->createInstances('acm_credentials_info', 'Drupal\acm\AcmCredential');

    return $credentials;
  }

  /**
   * Collect information from hooks and create instances of entities.
   *
   * @param string $hook
   *   The hook to invoke.
   * @param class-string $class
   *   The class name to instantiate an instance.
   *
   * @return \Drupal\acm\AcmAbstractEntity[]
   *   Array of instances.
   */
  protected function createInstances(string $hook, string $class): array {
    $instances = [];

    $infos = [];
    $infos += $this->moduleHandler->invokeAll($hook);
    $this->moduleHandler->alter($hook, $infos);

    foreach ($infos as $info) {
      // @phpstan-ignore-next-line
      $instance = call_user_func([$class, 'fromInfo'], $info);
      if ($instance) {
        $instances[] = $instance;
      }
    }

    return $instances;
  }

}
