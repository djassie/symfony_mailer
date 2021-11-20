<?php

namespace Drupal\symfony_mailer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an EmailBuilder item annotation object.
 *
 * @Annotation
 */
class EmailBuilder extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * Array of sub-types.
   *
   * The array key is the sub-type value and the value is the human-readable
   * label.
   *
   * @var string[]
   */
  public array $sub_types = [];

  /**
   * Whether the plugin is associated with a config entity.
   */
  public bool $has_entity = FALSE;

}
