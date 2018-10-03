<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Asset\Widget\Widget;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\GoogleAnalytics\Controller\StatisticsController;
use Bolt\Extension\Bolt\GoogleAnalytics\Provider\GoogleAnalyticsProvider;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Bolt\Translation\Translator as Trans;
use Bolt\Version;
use Google_Auth_Exception as AuthException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Google Analytics extension loader.
 *
 * @author Aaron Valandra <avaland@woopta.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GoogleAnalyticsExtension extends SimpleExtension
{
    /**
     * {@inheritdoc}
     */
    protected function registerMenuEntries()
    {
        $config = $this->getConfig();
        if (!$config['backend']) {
            return [];
        }

        $menu = (new MenuEntry('google', 'google-analytics'))
            ->setLabel(Trans::__('Statistics'))
            ->setIcon('fa:area-chart');

        return [$menu];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAssets()
    {
        $config = $this->getConfig();
        $assets = [];

        if ($config['webproperty']) {
            $analyticsCode = Snippet::create()
                ->setCallback([$this, 'analyticsCallback'])
                ->setZone(Zone::FRONTEND)
                ->setLocation(Target::END_OF_HEAD)
            ;
            $assets[] = $analyticsCode;
        }

        if ((bool) $config['widget']) {
            $widget = Widget::create()
                ->setCallback([$this, 'widgetCallback'])
                ->setZone(Zone::BACKEND)
                ->setLocation('dashboard_aside_top')
            ;
            $assets[] = $widget;
        }

        return $assets;
    }

    /**
     * Widget render callback.
     *
     * @return string
     */
    public function widgetCallback()
    {
        $app = $this->getContainer();
        /** @var Handler\GoogleAnalyticsHandler $handler */
        $handler = $app['ga.handler.googleAnalytics'];

        try {
            // Grab client to get access token that can be used in JS charts
            $client = $handler->connect();
        } catch (AuthException $e) {
            return $e->getMessage();
        }
        $context = [
            'token'          => $client->getAccessToken(),
            'locale'         => substr($app['locale'], 0, 2),
            'profile'        => $handler->getProfileID(),
            'statisticspage' => $app['ga.config']->isBackend(),
            'webpath'        => $this->getWebDirectory()->getPath(),
        ];

        // Render the template, and return the results
        return $this->renderTemplate('@GoogleAnalytics/widget.twig', $context);
    }

    /**
     * Analytics render callback.
     *
     * @return string
     */
    public function analyticsCallback()
    {
        $config = $this->getConfig();
        // Set the webproperty to be used whether or not universal is used or not
        $context['webproperty'] = $config['webproperty'];
        $context['anonymize_ip'] = $config['anonymize_ip'];

        // Check to see if universal is set
        if ((bool) $config['universal']) {
            $context['domainname'] = $config['universal_domainname'];

            return $this->renderTemplate('@GoogleAnalytics/universal.twig', $context);
        }

        $app = $this->getContainer();
        /** @var Request $request */
        $request = $app['request_stack']->getCurrentRequest();
        if ($request === null) {
            return null;
        }
        // Get full url for the current website
        $context['domainname'] = $request->getHttpHost();

        return $this->renderTemplate('@GoogleAnalytics/normal.twig', $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function registerTwigPaths()
    {
        return [
            'templates' => ['namespace' => 'GoogleAnalytics'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function registerBackendControllers()
    {
        $baseUrl = Version::compare('3.2.999', '<')
            ? '/extensions/google-analytics'
            : '/extend/google-analytics'
        ;

        return [
            $baseUrl => new StatisticsController($this->getWebDirectory()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceProviders()
    {
        return [
            $this,
            new GoogleAnalyticsProvider($this->getConfig()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultConfig()
    {
        return [
            'webproperty'           => null,
            'universal'             => true,
            'universal_domainname'  => null,
            'backend'               => true,
            'widget'                => false,
            'ga_profile_id'         => null,
            'key_file'              => null,
            'service_account_email' => null,
        ];
    }
}
