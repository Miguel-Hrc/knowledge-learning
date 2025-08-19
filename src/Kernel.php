<?php

namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Doctrine\ODM\MongoDB\DocumentManager;
use MongoDB\Client;

/**
 * Custom Symfony Kernel class.
 *
 * Handles the configuration of services and routes dynamically based on:
 * - The current environment (dev, prod, test, etc.)
 * - The database type used (ORM/MySQL or MongoDB)
 *
 * This kernel imports different configuration files depending on environment variables
 * and ensures that both ORM and MongoDB setups can coexist.
 */
class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    /**
     * Configures the service container.
     *
     * Imports configuration files based on:
     * - Global packages
     * - Environment-specific packages
     * - Database type (ORM or MongoDB) determined by $_ENV['USE_MONGODB']
     * - Additional environment-specific service files
     *
     * @param ContainerConfigurator $container The container configurator
     */
    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../config/packages/*.yaml');
        $container->import('../config/packages/' . $this->environment . '/*.yaml');

        if ($_ENV['USE_MONGODB'] === 'true') {
            $container->import('../config/packages/mongodb/*.yaml');
            $container->import('../config/services/services_mongo.yaml');

            if ($this->environment === 'test') {
                $container->import('../config/packages/test/*.yaml');
            }
        } else {
            $container->import('../config/packages/orm/*.yaml');
            $container->import('../config/services/services_orm.yaml');

            if ($this->environment === 'test') {
                $container->import('../config/packages/test/*.yaml');
            }
        }

        $container->import('../config/services.yaml');

        if (file_exists($this->getProjectDir() . '/config/services_' . $this->environment . '.yaml')) {
            $container->import('../config/services_' . $this->environment . '.yaml');
        }
    }

    /**
     * Configures the application routes.
     *
     * Imports route configuration files based on:
     * - Environment-specific routes
     * - Global routes
     * - The main routes.yaml file
     *
     * @param RoutingConfigurator $routes The routing configurator
     */
    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../config/{routes}/' . $this->environment . '/*.yaml');
        $routes->import('../config/{routes}/*.yaml');
        $routes->import('../config/routes.yaml');
    }
}