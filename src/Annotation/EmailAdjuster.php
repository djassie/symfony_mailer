<?php

namespace Drupal\symfony_mailer\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an EmailAdjuster item annotation object.
 *
 * @Annotation
 */
class EmailAdjuster extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * The label of the plugin.
   *
   * @ingroup plugin_translatable
   */
  public string $label;

}
