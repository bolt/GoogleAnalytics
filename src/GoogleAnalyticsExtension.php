<?php
// Google Analytics extension for Bolt

namespace Bolt\Extension\Bolt\GoogleAnalytics;

use Bolt\Asset\Snippet\Snippet;
use Bolt\Asset\Target;
use Bolt\Controller\Zone;
use Bolt\Extension\SimpleExtension;
use Bolt\Menu\MenuEntry;
use Eloquent\Pathogen\Exception\EmptyPathAtomException;
use Silex\ControllerCollection;
use Bolt\Translation\Translator as Trans;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Loader as TranslationLoader;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

                    $path = $app['resources']->getPath('extensions/' . $fileInfo->getPath());

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
        //Block unauthorized access...
        if (!$app['users']->isAllowed('dashboard')) {
            throw new AccessDeniedException('Logged in user does not have the correct rights to use this class.');
        }

        $config = $this->getConfig();

        list($analytics, $token) = $this->getService($config);

        $profile_id = $this->getFirstprofileId($analytics, $config['ga_profile_id']);

        $data = [
            "locale" => substr($app['locale'], 0, 2),
            "token" => $token,
            "profile" => $profile_id,
            'webpath' => $app['extensions']->get('Bolt/GoogleAnalytics')->getWebDirectory()->getPath(),
        ];

        $html = $this->renderTemplate("base.twig", $data);

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
            return $this->renderTemplate("universal.twig", $data);
        }

        $data['domainname'] = $app['request_stack']->getCurrentRequest()->server->get('HTTP_HOST');

        return $this->renderTemplate("normal.twig", $data);
    }

    private function getFirstprofileId(&$analytics, $specified_profile_id) {
        // Get the user's first view (profile) ID.

        // Get the list of profiles for the authorized user.
        $profiles = $analytics->management_profiles->listManagementProfiles("~all", "~all");

        if (count($profiles->getItems()) > 0) {
            $items = $profiles->getItems();

            if (! empty($specified_profile_id)) {
                foreach ($items as $item) {
                    if ($specified_profile_id == $item->getId()) {
                        return $specified_profile_id;
                    }
                }

                return 'The profile you specified is incorrect. Please re-check your profile ID OR specify no ID';
            }

            return $items[0]->getId();

        } else {
            return 'No profiles found for this user. Please check p12 key and email is correct';
        }
    }

    private function getService(array $config)
    {
        $app = $this->getContainer();

        if (empty($config['service_account_email'])) {
            return "service_account_email not set in config.yml.";
        }

        if (empty($config['key_file'])) {
            return "key_file not set in config.yml.";
        }

        $service_account_email = $config['service_account_email']; //Email Address
        $key_file = $config['key_file']; //key.p12

        try {
            $path = $app['resources']->getPath('extensionsconfig/' . $key_file);
        } catch (EmptyPathAtomException $e) {
            return "Please use the filename only, and include the file in app/config/extenstions/.";
        }

        if (!file_exists($path)) {
            return "Key file not found in app/config/extenstions/, please place the file there.";
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

        return [$analytics, $client->getAccessToken()];
    }
}
