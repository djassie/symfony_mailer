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
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * List of sub-types.
   *
   * Only present for a builder representing an email type.
   *
   * @var string[]
   */
  public $sub_types;

  /**
   * Whether the plugin is associated with a config entity.
   *
   * Only present for a builder representing an email type.
   *
   * @var bool
   */
  public $has_entity = FALSE;

}
