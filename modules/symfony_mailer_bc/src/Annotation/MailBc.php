<?php

namespace Drupal\symfony_mailer_bc\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a MailBC item annotation object.
 *
 * @Annotation
 */
class MailBc extends Plugin {

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

}
