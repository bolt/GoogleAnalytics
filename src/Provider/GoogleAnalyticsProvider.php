<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics\Provider;

use Bolt\Extension\Bolt\GoogleAnalytics\Config\Config;
use Bolt\Extension\Bolt\GoogleAnalytics\Handler\GoogleAnalyticsHandler;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Google Analytics extension service provider.
 *
 * @author Aaron Valandra <avaland@woopta.com>
 */
class GoogleAnalyticsProvider implements ServiceProviderInterface
{
    /** @var array $config */
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        /**
         * Config class
         */
        $app['ga.config'] = $app->share(
            function () {
                return new Config($this->config);
            }
        );

        /**
         * Google Analytics data handler
         */
        $app['ga.handler.googleAnalytics'] = $app->share(
            function ($app) {
                return new GoogleAnalyticsHandler(
                    $app['filesystem'],
                    $app['path_resolver'],
                    $app['ga.config']
                );
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
    }
}
