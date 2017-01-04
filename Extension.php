<?php
// Google Analytics extension for Bolt

namespace Bolt\Extension\Bolt\GoogleAnalytics;

use Bolt\Translation\Translator as Trans;
use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\Translation\Loader as TranslationLoader;
use Bolt\Extensions\Snippets\Location as SnippetLocation;

class Extension extends \Bolt\BaseExtension
{

    public function getName()
    {
        return "Google Analytics";
    }

    function initialize() {

        $this->path = $this->app['config']->get('general/branding/path') . '/extensions/google-analytics';

        $this->app->match($this->path, array($this, 'GoogleAnalytics'));

        $this->app['htmlsnippets'] = true;

        if ($this->app['config']->getWhichEnd()=='frontend') {
            $this->addSnippet('endofhead', 'insertAnalytics');
        } else {
            $this->app->before(array($this, 'before'));
        }

        if (isset($this->config['backend']) && $this->config['backend']) {
            $this->addMenuOption(Trans::__('Statistics'), $this->app['paths']['bolt'] . 'extensions/google-analytics', "fa:area-chart");
        }

    }

    public function before()
    {
        $this->translationDir = __DIR__.'/locales/' . substr($this->app['locale'], 0, 2);
        if (is_dir($this->translationDir))
        {
            $iterator = new \DirectoryIterator($this->translationDir);
            foreach ($iterator as $fileInfo)
            {
                if ($fileInfo->isFile())
                {
                    $this->app['translator']->addLoader('yml', new TranslationLoader\YamlFileLoader());
                    $this->app['translator']->addResource('yml', $fileInfo->getRealPath(), $this->app['locale']);
                }
            }
        }
    }

    public function GoogleAnalytics()
    {

        $this->addJavascript('assets/es6-promise.min.js', array('late' => true));
        $this->addJavascript('assets/active-users.js', array('late' => true));
        $this->addJavascript('assets/date-range-selector.js', array('late' => true));
        $this->addJavascript('assets/moment-with-locales.min.js', array('late' => true));
        $this->addJavascript('assets/Chart.min.js', array('late' => true));
        $this->addJavascript('assets/googleanalytics.js', array('late' => true, 'priority' => 1000));
        $this->addCss('assets/styles.css');

        $data = [
            "locale" => substr($this->app['locale'], 0, 2),
            "token" => $this->getService(),
            "profile" => $this->config['ga_profile_id']
        ];

        $this->app['twig.loader.filesystem']->addPath(__DIR__.'/views/', 'GoogleAnalytics');
        $html = $this->app['render']->render("@GoogleAnalytics/base.twig", $data);

        return new Response($html);

    }


    public function insertAnalytics()
    {

        if (empty($this->config['webproperty'])) {
            $this->config['webproperty'] = "property-not-set";
        }

        if ($this->config['universal']) {

        $html = <<< EOM
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', '%webproperty%', '%domainname%');%displayfeatures%
        ga('send', 'pageview');
    </script>
EOM;

        } else {

        $html = <<< EOM

    <script type="text/javascript">

      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', '%webproperty%']);
      _gaq.push(['_setDomainName', '%domainname%']);
      _gaq.push(['_trackPageview']);

      (function() {
          var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
          ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
          var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();

    </script>
EOM;
    }

        $html = str_replace("%webproperty%", $this->config['webproperty'], $html);
        $html = str_replace("%displayfeatures%", ( $this->config['universal_displayfeatures'] ? " ga('require','displayfeatures');" : '' ), $html);
        $html = str_replace("%domainname%", ( $this->config['universal'] ? $this->config['universal_domainname'] : $_SERVER['HTTP_HOST'] ), $html);

        return new \Twig_Markup($html, 'UTF-8');

    }

    private function getService()
    {

        if (empty($this->config['service_account_email'])) { return "service_account_email not set in config.yml."; }
        if (empty($this->config['key_file_location'])) { return "key_file_location not set in config.yml."; }
        if (empty($this->config['ga_profile_id'])) { return "ga_profile_id not set in config.yml."; }

        require_once(__DIR__.'/Google/autoload.php');

        $service_account_email = $this->config['service_account_email']; //Email Address
        $key_file_location = $this->config['key_file_location']; //key.p12

        // Create and configure a new client object.
        $client = new \Google_Client();
        $client->setApplicationName("HelloAnalytics");
        $analytics = new \Google_Service_Analytics($client);

        // Read the generated client_secrets.p12 key.
        $key = file_get_contents($key_file_location);
        $cred = new \Google_Auth_AssertionCredentials(
            $service_account_email,
            array(\Google_Service_Analytics::ANALYTICS_READONLY),
            $key
        );

        $client->setAssertionCredentials($cred);

        if($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion($cred);
        }

        return $client->getAccessToken();
    }

}
