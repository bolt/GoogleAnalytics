<?php


namespace Bolt\Extension\Bolt\GoogleAnalytics\Provider;


use Bolt\Extension\Bolt\GoogleAnalytics\Action\StatisticsAction;
use Bolt\Extension\Bolt\GoogleAnalytics\Config\Config;
use Bolt\Extension\Bolt\GoogleAnalytics\Handler\GoogleAnalyticsHandler;
use Bolt\Extension\Bolt\GoogleAnalytics\Snippet\AnalyticsSnippet;
use Bolt\Filesystem\Handler\DirectoryInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Class GoogleAnalyticsProvider
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Provider
 */
class GoogleAnalyticsProvider implements ServiceProviderInterface
{

    /** @var array $config */
    protected $config;

    /** @var DirectoryInterface $directory */
    protected $directory;

    public function __construct($config, DirectoryInterface $directory)
    {
        $this->config = $config;
        $this->directory = $directory;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     * @param Application $app
     */
    public function register(Application $app)
    {

        /**
         * This registers translations for the translator. This can be gotten rid of in version
         * in 3.2(?) since there should be an automatic translator...
         */
        $translationDirectory = $this->directory->getDir('translations');
        if ($translationDirectory->exists()) {
            foreach ($translationDirectory->getContents(true) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    list($domain, $extension) = explode('.', $fileInfo->getFilename());
                    $path = $app['resources']->getPath('extensions/' . $fileInfo->getPath());
                    $app['translator']->addResource($extension, $path, $domain);
                }
            }
        }

        /**
         * Config class
         */
        $app['ga.config.config'] = $app->share(
            function () {
                return new Config($this->config);
            }
        );

        /**
         * Snippets service...
         */
        $app['ga.snippet.analytics'] = $app->share(
            function ($app) {
                return new AnalyticsSnippet(
                    $app['twig'],
                    $app['request_stack'],
                    $app['ga.config.config']
                );
            }
        );

        /**
         * Google Analytics data handler
         */
        $app['ga.handler.googleAnalytics'] = $app->share(
            function ($app) {
                return new GoogleAnalyticsHandler(
                    $app['ga.config.config'],
                    $app['resources']
                );
            }
        );

        /**
         * Statistics controller action
         */
        $app['ga.action.statistics'] = $app->share(
            function ($app) {
                return new StatisticsAction(
                    $app['twig'],
                    $app['ga.handler.googleAnalytics'],
                    $app['locale'],
                    $app['extensions']->get('Bolt/GoogleAnalytics')->getWebDirectory()->getPath()
                );
            }
        );


    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     * @param Application $app
     */
    public function boot(Application $app)
    {
    }
}