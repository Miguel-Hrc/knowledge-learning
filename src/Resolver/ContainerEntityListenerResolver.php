<?php

namespace App\Resolver;

use Doctrine\Bundle\DoctrineBundle\Mapping\ContainerEntityListenerResolver as BaseResolver;

/**
 * Custom entity listener resolver that extends Doctrine's ContainerEntityListenerResolver.
 *
 * This class can be used to override or extend the default behavior of
 * Doctrine's entity listener resolution using the Symfony service container.
 */
class ContainerEntityListenerResolver extends BaseResolver
{
    // This class currently inherits all functionality from BaseResolver
    // and can be extended in the future for custom behavior.
}