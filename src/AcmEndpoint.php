<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class AcmEndpoint.
 *
 * Endpoint implementation.
 *
 * @package Drupal\acm
 */
class AcmEndpoint extends AcmAbstractEntity {

  /**
   * The URL.
   *
   * @var string
   */
  protected string $url;

  /**
   * The HTTP Auth user.
   *
   * @var string
   */
  protected string $authUser;

  /**
   * The HTTP Auth password.
   *
   * @var string
   */
  protected string $authPass;

  /**
   * Array of headers.
   *
   * @var string[]
   */
  protected array $headers;

  /**
   * Construct.
   *
   * @param string $name
   *   Name.
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   Label.
   * @param array<mixed> $info
   *   Info.
   */
  public function __construct(string $name, string|TranslatableMarkup $label, array $info) {
    parent::__construct($name, $label);
    $this->url = $info['url'];
    $this->headers = $info['headers'] ?? [];
    $this->authUser = $info['user'] ?? '';
    $this->authPass = $info['pass'] ?? '';
  }

  /**
   * Get URL.
   */
  public function getUrl(): string {
    return $this->url;
  }

  /**
   * Get headers.
   *
   * @return array<mixed>
   *   Headers.
   */
  public function getHeaders(): array {
    return $this->headers;
  }

  /**
   * Get HTTP Auth user.
   */
  public function getAuthUser(): string {
    return $this->authUser;
  }

  /**
   * Get HTTP Auth password.
   */
  public function getAuthPass(): string {
    return $this->authPass;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getRequiredKeys(): array {
    $required_keys = ['url', 'user', 'pass', 'headers'];
    return array_merge($required_keys, parent::getRequiredKeys());
  }

  /**
   * {@inheritdoc}
   */
  protected static function getDefaultValues(): array {
    return [
      'headers' => [],
      'user' => '',
      'pass' => '',
    ];
  }

}
