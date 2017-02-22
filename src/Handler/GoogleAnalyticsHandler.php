<?php


namespace Bolt\Extension\Bolt\GoogleAnalytics\Handler;


use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\GoogleAnalytics\Config\Config;
use Eloquent\Pathogen\Exception\EmptyPathAtomException;
use Google_Client;
use Google_Service_Analytics;
use Google_Auth_AssertionCredentials;
use Exception;

/**
 * Class GoogleAnalyticsHandler
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Handler
 * Handler to connect to Google Analytics and generate the dashboard
 */
class GoogleAnalyticsHandler
{
    /** @var Config $config */
    protected $config;

    /** @var ResourceManager $resource */
    protected $resource;

    /** @var Google_Client $client */
    private $client;

    /** @var Google_Service_Analytics $analytics */
    private $analytics;

    public function __construct(Config $config, ResourceManager $resource)
    {
        $this->config = $config;
        $this->resource = $resource;
    }

    /**
     * @return Google_Client
     * @throws Exception
     * This function connects to Google Analytics and returns the client
     */
    public function connect()
    {
        $service_account_email = $this->config->getServiceAccountEmail(); //Email Address
        $key_file = $this->config->getKeyFile(); //key.p12

        //Verify service account email is in config.yml
        if (empty($service_account_email)) {
            throw new Exception("service_account_email not set in config.yml.");
        }

        //Verify key file is in config.yml
        if (empty($key_file)) {
            throw new Exception("key_file not set in config.yml.");
        }

        //$path may throw error if user specifies FULL path, so catch error and give user a better error message
        try {
            $path = $this->resource->getPath('extensionsconfig/' . $key_file);
        } catch (EmptyPathAtomException $e) {
            throw new Exception("Please use the filename only, and include the file in app/config/extenstions/.");
        }

        //Verify key file exists
        if (!file_exists($path)) {
            throw new Exception("Key file not found in app/config/extenstions/, please place the file there.");
        }

        require_once(__DIR__.'/../../Google/autoload.php');

        // Create and configure a new client object.
        $client = new Google_Client();
        $client->setApplicationName("Statistics");
        $analytics = new Google_Service_Analytics($client);

        // Read the generated client_secrets.p12 key.
        $key = file_get_contents($path);
        $cred = new Google_Auth_AssertionCredentials(
            $service_account_email,
            array(Google_Service_Analytics::ANALYTICS_READONLY),
            $key
        );

        $client->setAssertionCredentials($cred);

        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        $this->setClient($client);

        $this->setAnalytics($analytics);

        return $client;
    }

    /**
     * @return mixed
     * @throws Exception
     * This checks to see if the specified profile id is correct,
     * and if it isn't then throw exception. If nothing set, then it grabs first id.
     */
    public function getProfileID() {

        // Get the list of profiles for the authorized user.
        $profiles = $this->getAnalytics()->management_profiles->listManagementProfiles("~all", "~all");

        //Verify user has profiles
        if (count($profiles->getItems()) > 0) {

            //Get all of the profiles associated with user
            $items = $profiles->getItems();

            //Get the specified profile ID
            $specified_profile_id = $this->config->getGaProfileId();

            //Check to see if Bolt Extension configuration has profile id already
            if (! empty($specified_profile_id)) {
                //Loop through each profile and check to see if the id is correctly set
                foreach ($items as $item) {
                    if ($specified_profile_id == $item->getId()) {
                        return $specified_profile_id;
                    }
                }

                //Throw error is profile ID is incorrect
                throw new Exception('The profile you specified is incorrect. Please re-check your profile ID OR specify no ID');
            }

            //Return first profile ID
            return $items[0]->getId();

        } else {
            //If no profiles set at all then throw error
            throw new Exception('No profiles found for this user. Please check p12 key and email is correct');
        }
    }

    /**
     * @return Google_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param Google_Client $client
     * @return GoogleAnalyticsHandler
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return Google_Service_Analytics
     */
    public function getAnalytics()
    {
        return $this->analytics;
    }

    /**
     * @param Google_Service_Analytics $analytics
     * @return GoogleAnalyticsHandler
     */
    public function setAnalytics($analytics)
    {
        $this->analytics = $analytics;
        return $this;
    }

}
