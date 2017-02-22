<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics\Action;

use Bolt\Extension\Bolt\GoogleAnalytics\Handler\GoogleAnalyticsHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig_Environment;

/**
 * Class StatisticsAction
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Action
 * Action called when route /extensions/google-analytics is called
 */
class StatisticsAction
{
    /** @var Twig_Environment $view */
    protected $view;

    /** @var GoogleAnalyticsHandler $handler */
    protected $handler;

    /** @var $locale */
    protected $locale;

    /** @var string $webPath */
    protected $webPath;

    /**
     * StatisticsAction constructor.
     * @param Twig_Environment $view
     * @param GoogleAnalyticsHandler $handler
     * @param $locale
     * @param $webPath
     */
    public function __construct(Twig_Environment $view, GoogleAnalyticsHandler $handler, $locale, $webPath)
    {
        $this->view = $view;
        $this->handler = $handler;
        $this->locale = $locale;
        $this->webPath = $webPath;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function displayStatistics(Request $request)
    {

        //Grab client to get access token that can be used in JS charts
        $client = $this->handler->connect();

        //Get correct profile ID for JS charts
        $profile_id = $this->handler->getProfileID();

        $data = [
            "locale" => substr($this->locale, 0, 2),
            "token" => $client->getAccessToken(),
            "profile" => $profile_id,
            'webpath' => $this->webPath,
        ];

        return $this->view->render("@GoogleAnalytics/base.twig", $data);
    }
}