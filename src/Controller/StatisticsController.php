<?php


namespace Bolt\Extension\Bolt\GoogleAnalytics\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class StatisticsController
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Controller
 */
class StatisticsController implements ControllerProviderInterface
{
    /** @var Application $app */
    protected $app;

    /**
     * StatisticsController constructor.
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->match('/google-analytics', [$app['ga.action.statistics'], "displayStatistics"]);

        //This must be ran, current user is not set at this time.
        $controller->before([$this, 'before']);
        return $controller;
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return null|RedirectResponse
     */
    public function before(Request $request, Application $app)
    {
        if (!$app['users']->isAllowed('dashboard')) {
            /** @var UrlGeneratorInterface $generator */
            $generator = $app['url_generator'];
            return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_SEE_OTHER);
        }
        return null;
    }
}