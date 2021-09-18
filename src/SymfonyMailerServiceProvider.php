<?php

namespace Drupal\symfony_mailer;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Modifies the language manager service.
 */
class SymfonyMailerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $definition = $container->getDefinition('plugin.manager.mail');
    $definition->setClass('Drupal\symfony_mailer\MailBuilderManager')
      ->setArguments(array_slice($definition->getArguments(), 0, 3))
      ->addArgument(new Reference('email_factory'));
  }

}
