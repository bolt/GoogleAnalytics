<?php
// Google Analytics extension for Bolt

namespace Bolt\Extension\Bolt\GoogleAnalytics;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Silex\ControllerCollection;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader as TranslationLoader;

class GoogleAnalyticsExtension extends SimpleExtension
{

    protected function registerServices(Application $app)
    {
        //Register translations in the service provider.
        $translationDirectory = $this->getBaseDirectory()->getDir('translations');
        if ($translationDirectory->exists()) {
            foreach ($translationDirectory->getContents(true) as $fileInfo) {
                if ($fileInfo->isFile()) {
                    list($domain, $extension) = explode('.', $fileInfo->getFilename());

                    $path = $app['resources']->getPath('extensions' . DIRECTORY_SEPARATOR . $fileInfo->getPath());

                    $app['translator']->addResource($extension, $path, $domain);
                }
            }
        }

        parent::registerServices($app);
    }

    protected function registerBackendRoutes(ControllerCollection $collection)
    {
        $collection->match('/extensions/google-analytics', [$this, 'googleAnalytics']);
    }

    protected function registerTwigPaths()
    {
        return [
            'templates'
        ];
    }

    protected function registerMenuEntries()
    {
        $menu = (new MenuEntry('google', '/bolt/extensions/google-analytics'))
            ->setLabel(Trans::__('Statistics'))
            ->setIcon('fa:area-chart');

        return [
            $menu
        ];
    }
    
    protected function registerAssets()
    {
        $analyticsCode = (new Snippet())
            ->setZone(Zone::FRONTEND)
            ->setLocation(Target::END_OF_HEAD)
            ->setCallback([$this, 'insertAnalytics']);

        return [
            $analyticsCode
        ];
    }

    public function googleAnalytics(Application $app, Request $request)
    {
        $config = $this->getConfig();

        $data = [
            "locale" => substr($this->app['locale'], 0, 2),
            "token" => $this->getService($config),
            "profile" => $config['ga_profile_id'],
            'webpath' => $app['extensions']->get('Bolt/GoogleAnalytics')->getWebDirectory()->getPath(),
        ];

        $html = $app['twig']->render("base.twig", $data);

        return new Response($html);

    }

    protected function getDefaultConfig()
    {
        return [
            'webproperty' => "property-not-set"
        ];
    }

    public function insertAnalytics()
    {
        $config = $this->getConfig();
        $app = $this->getContainer();

        $data = [
            'webproperty' => $config['webproperty']
        ];

        if ($config['universal']) {
            $data['domainname'] = $config['universal_domainname'];
            return $app['twig']->render("universal.twig", $data);
        }

        $data['domainname'] = $_SERVER['HTTP_HOST'];
        return $app['twig']->render("normal.twig", $data);
    }

    private function getService(array $config)
    {
        $app = $this->getContainer();

        if (empty($config['service_account_email'])) {
            return "service_account_email not set in config.yml.";
        }

        if (empty($config['key_file_location'])) {
            return "key_file_location not set in config.yml.";
        }

        if (empty($config['ga_profile_id'])) {
            return "ga_profile_id not set in config.yml.";
        }

        $service_account_email = $config['service_account_email']; //Email Address
        $key_file_location = $config['key_file_location']; //key.p12

        $path = $app['resources']->getPath('config');

        $path .= DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . $key_file_location;

        if (!file_exists($path)) {
            return "Key file not found in app/config/extenstions/!";
        }

        require_once(__DIR__.'/../Google/autoload.php');

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("HelloAnalytics");
        $analytics = new \Google_Service_Analytics($client);

        // Read the generated client_secrets.p12 key.
        $key = file_get_contents($path);
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_email,
            array(\Google_Service_Analytics::ANALYTICS_READONLY),
            $key
        );

        $client->setAssertionCredentials($cred);

        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        return $client->getAccessToken();
    }
}
