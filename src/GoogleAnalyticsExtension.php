<?php
// Google Analytics extension for Bolt

namespace Bolt\Extension\Bolt\GoogleAnalytics;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\GoogleAnalytics\Controller\StatisticsController;
use Bolt\Extension\Bolt\GoogleAnalytics\Provider\GoogleAnalyticsProvider;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Symfony\Component\Translation\Loader as TranslationLoader;

/**
 * Class GoogleAnalyticsExtension
 * @package Bolt\Extension\Bolt\GoogleAnalytics
 */
class GoogleAnalyticsExtension extends SimpleExtension
{

    /**
     * @return array
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new GoogleAnalyticsProvider($this->getConfig(), $this->getBaseDirectory())
        ];
    }

    /**
     * @return array
     */
    protected function registerBackendControllers()
    {
        return [
            'extend/google-analytics' => new StatisticsController($this->getContainer()),
        ];
    }

    /**
     * @return array
     * If backend is set to false, don't load the menu link.
     */
    protected function registerMenuEntries()
    {
        $app = $this->getContainer();

        if (! $app['ga.config.config']->isBackend()) {
            return [];
        }

        $menu = (new MenuEntry('google', 'google-analytics'))
            ->setLabel(Trans::__('Statistics'))
            ->setIcon('fa:area-chart');

        return [
            $menu
        ];
    }

    /**
     * @return array
     */
    protected function registerAssets()
    {
        $app = $this->getContainer();

        $assets = [];

        if ($app['ga.config.config']->getWebproperty()) {
            $analyticsCode = (new Snippet())
                ->setZone(Zone::FRONTEND)
                ->setLocation(Target::END_OF_HEAD)
                ->setCallback([$app['ga.snippet.analytics'], "insertAnalytics"]);

            $assets[] = $analyticsCode;
        }

        if ($app['ga.config.config']->isWidget()) {
            $widgetObj = new \Bolt\Asset\Widget\Widget();
            $widgetObj
                ->setZone('backend')
                ->setLocation('dashboard_aside_top')
                ->setCallback([$this, 'widget'])
                ->setCallbackArguments([])
                ->setDefer(false)
            ;
            $assets[] = $widgetObj;
        }

        return $assets;
    }

    public function widget()
    {
        $app = $this->getContainer();

        //Grab client to get access token that can be used in JS charts
        $client = $app['ga.handler.googleAnalytics']->connect();

        $twigvars = [
            'token'          => $client->getAccessToken(),
            'locale'         => substr($app['locale'], 0, 2),
            'profile'        => $app['ga.handler.googleAnalytics']->getProfileID(),
            'statisticspage' => $app['ga.config.config']->isBackend(),
            'webpath'        => $app['extensions']->get('Bolt/GoogleAnalytics')->getWebDirectory()->getPath()
        ];

        // Render the template, and return the results
        return $this->renderTemplate('@GoogleAnalytics/widget.twig', $twigvars);
    }

    /**
     * @return array
     */
    protected function registerTwigPaths()
    {
        return [
            'templates' => ['namespace' => 'GoogleAnalytics']
        ];
    }
}
