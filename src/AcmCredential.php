<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class AcmCredential.
 *
 * Describes credential entity.
 *
 * @package Drupal\acm
 */
class AcmCredential extends AcmAbstractEntity {

  /**
   * Array of parameters.
   *
   * @var \Drupal\acm\AcmParameter[]
   */
  protected array $parameters;

  /**
   * Contruct.
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

    $this->parameters = !empty($info['parameters']) ? static::createParameterInstances($info['parameters']) : [];
  }

  /**
   * Get parameters.
   *
   * @return \Drupal\acm\AcmParameter[]
   *   Array of parameters.
   */
  public function getParameters(): array {
    return $this->parameters;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getRequiredKeys(): array {
    return array_merge(['parameters'], parent::getRequiredKeys());
  }

  /**
   * {@inheritdoc}
   */
  protected static function sanitiseInfo($info): ?array {
    $info = parent::sanitiseInfo($info);

    if (empty($info['parameters'])) {
      return NULL;
    }

    return $info;
  }

  /**
   * Create parameter instances from provided parameter infos.
   *
   * @param array<mixed> $infos
   *   Array of paramter infos.
   *
   * @return \Drupal\acm\AcmParameter[]
   *   Array of parameter instances.
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  protected function createParameterInstances(array $infos): array {
    $instances = [];

    foreach ($infos as $info) {
      $instance = AcmParameter::fromInfo($info);
      if ($instance) {
        $instances[] = $instance;
      }
    }

    return $instances;
  }

}
