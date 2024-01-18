<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\encrypt\EncryptService;

/**
 * Class AcmCredentialsManager.
 *
 * Manages credentials through storage.
 *
 * @package Drupal\acm
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class AcmCredentialsManager {

  /**
   * The configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected Config $config;

  /**
   * The config factory used to load credentials per environment.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Config storage to cache credentials per environment.
   *
   * @var \Drupal\Core\Config\Config[]
   */
  protected array $storages;

  /**
   * Array of cached credentials data, keyed by the environment name.
   *
   * @var array<mixed>
   */
  protected array $credentials;

  /**
   * The encrypt service used to encrypt data.
   *
   * @var \Drupal\encrypt\EncryptService
   */
  protected EncryptService $encryptService;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected EntityTypeManager $entityTypeManager;

  /**
   * The info manager.
   *
   * Used to collect data about credentials entities.
   *
   * @var \Drupal\acm\AcmInfoManager
   */
  protected AcmInfoManager $infoManager;

  /**
   * The encrypt profile name used in the encrypt service to encrypt data.
   *
   * @var string|null
   */
  protected ?string $encryptProfile = NULL;

  /**
   * The current API environment.
   *
   * @var string|null
   */
  protected ?string $currentEnvironment = NULL;

  /**
   * AcmCredentialsManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\encrypt\EncryptService $encrypt_service
   *   The encrypt service.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\acm\AcmInfoManager $info_manager
   *   The info manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EncryptService $encrypt_service, EntityTypeManager $entity_type_manager, AcmInfoManager $info_manager) {
    $this->config = $config_factory->getEditable('acm.config');
    $this->configFactory = $config_factory;
    $this->encryptService = $encrypt_service;
    $this->entityTypeManager = $entity_type_manager;
    $this->infoManager = $info_manager;

    $this->currentEnvironment = $this->getCurrentEnvironment();
    $this->encryptProfile = $this->getEncryptProfileName();

    $this->initCredentials();
  }

  /**
   * Get current environment.
   *
   * @return string|null
   *   The current environment name.
   */
  public function getCurrentEnvironment(): ?string {
    return $this->config->get('current_environment');
  }

  /**
   * Set current environment.
   *
   * @param string $name
   *   The current environment name.
   */
  public function setCurrentEnvironment($name): void {
    $this->config->set('current_environment', $name)->save();
  }

  /**
   * Get current encryption profile.
   *
   * @return string|null
   *   The current encryption profile name.
   */
  public function getEncryptProfileName(): ?string {
    return $this->config->get('encrypt_profile');
  }

  /**
   * Set current encryption profile.
   *
   * @param string $name
   *   The current encryption profile name.
   */
  public function setEncryptProfileName($name): void {
    $this->config->set('encrypt_profile', $name)->save();
  }

  /**
   * Get all encryption profile entities.
   *
   * @return \Drupal\encrypt\EncryptionProfileInterface[]
   *   Array of all available encryption profile entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getAllEncryptProfiles(): array {
    /** @var \Drupal\encrypt\EncryptionProfileInterface[] $encryption_profiles */
    $encryption_profiles = $this->entityTypeManager->getStorage('encryption_profile')->loadMultiple();

    return $encryption_profiles;
  }

  /**
   * Initialise and cache all credentials.
   *
   * Used to load all credentials once when this class is instantiated.
   *
   * @SuppressWarnings(PHPMD.UnusedLocalVariable)
   */
  protected function initCredentials(): void {
    $envs = $this->infoManager->getEnvironments();
    foreach ($envs as $env) {
      $storage = $this->getEnvironmentStorage($env->getName());
      // @phpstan-ignore-next-line
      if (!$storage) {
        continue;
      }

      $values = $storage->get('credentials') ?? [];

      foreach ($values as $name => $value) {
        $processed = !empty($value) ? Json::decode($this->decrypt($value)) : NULL;

        if ($processed) {
          $processed['endpoint'] = $this->getEndpointByName($processed['endpoint']);
        }
        $this->credentials[$env->getName()][$name] = $processed;
      }
    }
  }

  /**
   * Find endpoint by name.
   *
   * @param string $name
   *   Endpoint name.
   *
   * @return \Drupal\acm\AcmEndpoint|null
   *   Endpoint instance or NULL if endpoint with this name does not exist.
   */
  protected function getEndpointByName(string $name): ?AcmEndpoint {
    $endpoints = $this->infoManager->getEndpoints();
    foreach ($endpoints as $endpoint) {
      if ($endpoint->getName() == $name) {
        return $endpoint;
      }
    }
    return NULL;
  }

  /**
   * Get a single credential.
   *
   * @param string $name
   *   The credential name.
   * @param string|null $environment
   *   (optional) Environment name. Defaults to NULL, meaning that the current
   *   environment will be used.
   *
   * @return mixed|null
   *   Credential data or NULL if credential with specified name was not found.
   */
  public function getCredential(string $name, string $environment = NULL): mixed {
    $environment = $environment ?? $this->currentEnvironment;
    return $this->credentials[$environment][$name] ?? NULL;
  }

  /**
   * Set credential data.
   *
   * @param string $name
   *   The credential name.
   * @param mixed $data
   *   The data value to set.
   * @param string|null $environment
   *   (optional) The environment to set the data. Defaults to NULL, meaning
   *   that the current environment will be used.
   */
  public function setCredential(string $name, mixed $data, string $environment = NULL): void {
    $environment = $environment ?? $this->currentEnvironment;
    $this->credentials[$environment][$name] = $data;
  }

  /**
   * Save all credentials into storage.
   *
   * @SuppressWarnings(PHPMD.ElseExpression)
   */
  public function saveAllCredentials(): void {
    foreach ($this->credentials as $env => $credentials) {
      $credentials_storage = $this->getEnvironmentStorage($env);
      $existing_credentials = $credentials_storage->get('credentials');

      foreach ($credentials as $name => $credential) {
        // @todo Improve handling of passed name or the whole object.
        $credentials[$name]['endpoint'] = (isset($credential['endpoint']) && $credential['endpoint'] instanceof AcmEndpoint) ?
          $credential['endpoint']->getName() : $credential['endpoint'];

        // Only invoke encryption on objects that were changed. This assessment
        // is required to avoid changes produced by encryption of the same
        // values: some encryption methods may produce different results for the
        // same input.
        $existing_credential = $existing_credentials[$name] ?? NULL;
        if (Json::encode($credentials[$name]) == $this->decrypt($existing_credential)) {
          $credentials[$name] = $existing_credential;
        }
        else {
          $credentials[$name] = $this->encrypt(Json::encode($credentials[$name]));
        }
      }

      $credentials_storage->set('credentials', $credentials);

      $credentials_storage->save();
    }
  }

  /**
   * Get all environments.
   *
   * @return \Drupal\acm\AcmEnvironment[]
   *   Array of environment instances.
   */
  public function getEnvironments(): array {
    return $this->infoManager->getEnvironments();
  }

  /**
   * Get environment storage.
   *
   * @param string $name
   *   Environment name.
   *
   * @return \Drupal\Core\Config\Config
   *   The config storage to store environment.
   */
  protected function getEnvironmentStorage(string $name): Config {
    $this->storages[$name] = $this->storages[$name] ?? $this->configFactory->getEditable('acm.credentials.' . $name);
    return $this->storages[$name];
  }

  /**
   * Encrypt data.
   *
   * @param string $data
   *   The data to encrypt.
   *
   * @return string
   *   Encrypted data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\encrypt\Exception\EncryptException
   */
  protected function encrypt(string $data): string {
    if (empty($this->encryptProfile)) {
      return $data;
    }

    /** @var \Drupal\encrypt\EncryptionProfileInterface|NULL $encrypt_profile */
    $encrypt_profile = $this->entityTypeManager->getStorage('encryption_profile')->load($this->encryptProfile);
    if (!$encrypt_profile) {
      return $data;
    }

    return $this->encryptService->encrypt($data, $encrypt_profile);
  }

  /**
   * Decrypt data.
   *
   * @param string $data
   *   The data to encrypt.
   *
   * @return string
   *   Decrypted data.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\encrypt\Exception\EncryptException
   * @throws \Drupal\encrypt\Exception\EncryptionMethodCanNotDecryptException
   */
  protected function decrypt(string $data): string {
    if (empty($this->encryptProfile)) {
      return $data;
    }

    /** @var \Drupal\encrypt\EncryptionProfileInterface|NULL $encrypt_profile */
    $encrypt_profile = $this->entityTypeManager->getStorage('encryption_profile')->load($this->encryptProfile);
    if (!$encrypt_profile) {
      return $data;
    }

    return $this->encryptService->decrypt($data, $encrypt_profile);
  }

}
