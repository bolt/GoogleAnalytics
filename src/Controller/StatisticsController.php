<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics\Controller;

use Bolt\Controller\Base;
use Bolt\Controller\Zone;
use Bolt\Extension\Bolt\GoogleAnalytics\Handler\GoogleAnalyticsHandler;
use Bolt\Filesystem\Handler\DirectoryInterface;
use Google_Auth_Exception as AuthException;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Statistics controller.
 *
 * @author Aaron Valandra <avaland@woopta.com>
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class StatisticsController extends Base
{
    /** @var DirectoryInterface */
    private $webPath;

    /**
     * Constructor.
     *
     * @param DirectoryInterface $webPath
     */
    public function __construct(DirectoryInterface $webPath)
    {
        $this->webPath = $webPath;
    }

    protected function addRoutes(ControllerCollection $c)
    {
        $c->value(Zone::KEY, Zone::BACKEND);

        $c->get('/', [$this, 'displayStatistics'])
            ->bind('displayStatistics')
            ->before([$this, 'before']);

        return $c;
    }

    /**
     * {@inheritdoc}
     */
    public function before()
    {
        if ($this->users()->isAllowed('dashboard')) {
            return null;
        }

        return new RedirectResponse($this->generateUrl('dashboard'), Response::HTTP_SEE_OTHER);
    }

    /**
     * @return string
     */
    public function displayStatistics()
    {
        /** @var GoogleAnalyticsHandler $handler */
        $handler = $this->app['ga.handler.googleAnalytics'];

        try {
            // Grab client to get access token that can be used in JS charts
            $token = $handler->connect()->getAccessToken();
            // Get correct profile ID for JS charts
            $profileId = $handler->getProfileID();
        } catch (AuthException $e) {
            $this->flashes()->error(sprintf('Unable to get Google API token: %s', $e->getMessage()));
            $token = null;
            $profileId = null;
        }

        $context = [
            'locale'  => substr($this->app['locale'], 0, 2),
            'token'   => $token,
            'profile' => $profileId,
            'webpath' => $this->webPath->getPath(),
        ];

        return $this->render('@GoogleAnalytics/base.twig', $context);
    }
}
