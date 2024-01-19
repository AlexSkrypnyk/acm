<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class AcmAbstractEntity.
 *
 * Abstract entity used by other classes.
 *
 * @package Drupal\acm
 */
class AcmAbstractEntity {

  /**
   * Entity (machine) name.
   */
  protected string $name;

  /**
   * Entity label.
   */
  protected string|TranslatableMarkup $label;

  /**
   * AcmAbstractEntity constructor.
   *
   * @param string $name
   *   Entity name to set.
   * @param string|\Drupal\Core\StringTranslation\TranslatableMarkup $label
   *   Entity label.
   */
  public function __construct(string $name, string|TranslatableMarkup $label) {
    $this->name = $name;
    $this->label = $label;
  }

  /**
   * Get entity name.
   *
   * @return string
   *   The name of the entity.
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Get entity label.
   *
   * @return string|\Drupal\Core\StringTranslation\TranslatableMarkup
   *   The label of the entity.
   */
  public function getLabel(): string|TranslatableMarkup {
    return $this->label;
  }

  /**
   * Create entity from info.
   *
   * @param array<mixed> $info
   *   Array of values.
   *
   * @return static|null
   *   Newly created entity instance or NULL if provided info did not contain
   *   all required data.
   */
  public static function fromInfo(array $info): ?static {
    $info = static::sanitiseInfo($info);
    // @phpstan-ignore-next-line
    return $info ? new static($info['name'], $info['label'], $info) : NULL;
  }

  /**
   * Get an array of all required keys.
   *
   * Extending classes should merge with this list of keys to validate.
   *
   * @return string[]
   *   Array of key names that are requried to be present in the info array.
   */
  protected static function getRequiredKeys(): array {
    return [
      'name',
      'label',
    ];
  }

  /**
   * Sanitise info array.
   *
   * @param array<mixed> $info
   *   The info array to sanitixse.
   *
   * @return array<mixed>|null
   *   Sanitised array based on required keys.
   */
  protected static function sanitiseInfo(array $info): ?array {
    $info_keys = static::getRequiredKeys();

    $info = array_intersect_key($info, array_flip($info_keys));

    $info += static::getDefaultValues();

    if (count($info) < count($info_keys)) {
      return NULL;
    }

    return $info;
  }

  /**
   * Get default values for fields that were not provided.
   *
   * @return array<mixed>
   *   Array of default values.
   */
  protected static function getDefaultValues(): array {
    return [];
  }

}
