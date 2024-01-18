<?php

declare(strict_types = 1);

namespace Drupal\acm;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class AcmParameter.
 *
 * Parameter used in credential definition.
 *
 * @package Drupal\acm
 */
class AcmParameter extends AcmAbstractEntity {

  /**
   * The parameter type.
   *
   * @var string
   */
  protected string $type;

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
    $this->type = $info['type'] ?? '';
  }

  /**
   * Get the type of the parameter.
   *
   * @return string
   *   Teh type.
   */
  public function getType(): string {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  protected static function getRequiredKeys(): array {
    return array_merge(['type'], parent::getRequiredKeys());
  }

  /**
   * {@inheritdoc}
   */
  protected static function sanitiseInfo($info): ?array {
    $info = parent::sanitiseInfo($info);

    if (empty($info['type'])) {
      return NULL;
    }

    return $info;
  }

}
