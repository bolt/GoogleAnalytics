<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics\Handler;

use Bolt\Configuration\PathResolver;
use Bolt\Extension\Bolt\GoogleAnalytics\Config\Config;
use Bolt\Filesystem\Manager;
use Google_Auth_AssertionCredentials as Credentials;
use Google_Auth_OAuth2 as OAuth2;
use Google_Client as Client;
use Google_Service_Analytics as Analytics;
use Google_Service_Analytics_Account as Account;
use RuntimeException;

/**
 * Handler to connect to Google Analytics and generate the dashboard
 *
 * @author Aaron Valandra <avaland@woopta.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GoogleAnalyticsHandler
{
    /** @var Manager */
    private $filesystem;
    /** @var PathResolver */
    private $pathResolver;
    /** @var Config */
    private $config;
    /** @var Client */
    private $client;
    /** @var Analytics */
    private $analytics;

    /**
     * Constructor.
     *
     * @param Manager      $filesystem
     * @param PathResolver $pathResolver
     * @param Config       $config
     */
    public function __construct(Manager $filesystem, PathResolver $pathResolver, Config $config)
    {
        $this->filesystem = $filesystem;
        $this->pathResolver = $pathResolver;
        $this->config = $config;
    }

    /**
     * This function connects to Google Analytics and returns the client
     *
     * @throws RuntimeException
     *
     * @return Client
     */
    public function connect()
    {
        // Email Address
        $serviceAccountEmail = $this->config->getServiceAccountEmail();
        // key.p12
        $keyFileName = $this->config->getKeyFile();

        // Verify service account email is in config.yml
        if (!$serviceAccountEmail) {
            throw new RuntimeException(sprintf(
                'The "service_account_email" parameter not set in %s/googleanalytics.bolt.yml',
                $this->pathResolver->resolve('%extensions_config%')
            ));
        }
        // Verify key file is in config.yml
        if (!$keyFileName) {
            throw new RuntimeException(sprintf(
                'The "key_file" parameter not set in %s/googleanalytics.bolt.yml',
                $this->pathResolver->resolve('%extensions_config%')
            ));
        }
        $keyFile = $this->filesystem->getFilesystem('extensions_config')->getFile($keyFileName);
        if (!$keyFile->exists()) {
            throw new RuntimeException('Key file not found in app/config/extensions/, please place the file there.');
        }
        // Read the generated client_secrets.p12 key.
        $key = $keyFile->read();

        // Invoke Google's autoloader … 'cause WTF Google!
        require_once __DIR__ . '/../../Google/autoload.php';

        // Create client credentials
        $credentials = new Credentials($serviceAccountEmail, [Analytics::ANALYTICS_READONLY], $key);
        // Create and configure a new client object.
        $this->client = new Client();
        $this->client->setApplicationName('Statistics');
        $this->client->setAssertionCredentials($credentials);

        // Create analytics service
        $this->analytics = new Analytics($this->client);

        /** @var OAuth2 $auth */
        $auth = $this->client->getAuth();
        if ($auth->isAccessTokenExpired()) {
            $auth->refreshTokenWithAssertion($credentials);
        }

        return $this->client;
    }

    /**
     * This checks to see if the specified profile ID is correct, and if it
     * isn't then throw an exception. If nothing set, then grab the first ID.
     *
     * @throws RuntimeException
     *
     * @return mixed
     */
    public function getProfileID()
    {
        // Get the list of profiles for the authorized user.
        $profiles = $this->analytics->management_profiles->listManagementProfiles('~all', '~all');

        // Get the specified profile ID
        // Check to see if Bolt Extension configuration has profile id already
        $specifiedProfileId = $this->config->getGaProfileId();
        if (!$specifiedProfileId) {
            throw new RuntimeException('The profile you specified is incorrect. Please re-check your profile ID, or specify no ID');
        }

        // If the service account has access to more than 1000 profiles
        if (count($profiles->getItems()) > 999) {
            return $specifiedProfileId;
        }

        // Verify user has profiles
        if (!count($profiles->getItems())) {
            //If no profiles set at all then throw error
            throw new RuntimeException('No profiles found for this user. Please check p12 key and email is correct');
        }

        // Get all of the profiles associated with user
        $items = $profiles->getItems();
        // Loop through each profile and check to see if the ID is correctly set
        foreach ($items as $item) {
            /** @var Account $item */
            if ($specifiedProfileId === $item->getId()) {
                return $specifiedProfileId;
            }
        }

        // Return first profile ID
        $item = $items[0];

        return $item->getId();
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Analytics
     */
    public function getAnalytics()
    {
        return $this->analytics;
    }
}
