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
            '/extensions' => new StatisticsController($this->getContainer()),
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

        $menu = (new MenuEntry('google', '/bolt/extensions/google-analytics'))
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

        $analyticsCode = (new Snippet())
            ->setZone(Zone::FRONTEND)
            ->setLocation(Target::END_OF_HEAD)
            ->setCallback([$app['ga.snippet.analytics'], "insertAnalytics"]);

        return [
            $analyticsCode
        ];
    }

    /**
     * @return array
     */
    protected function registerTwigPaths()
    {
        return ['templates'];
    }
}
