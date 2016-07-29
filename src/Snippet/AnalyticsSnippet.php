<?php


namespace Bolt\Extension\Bolt\GoogleAnalytics\Snippet;

use Bolt\Extension\Bolt\GoogleAnalytics\Config\Config;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Environment;

/**
 * Class AnalyticsSnippet
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Snippet
 * Snippet class to insert analytics on every page
 */
class AnalyticsSnippet
{
    /** @var Twig_Environment $view */
    protected $view;

    /** @var RequestStack $request */
    protected $request;

    /** @var Config $config */
    protected $config;

    public function __construct(
        Twig_Environment $view,
        RequestStack $request,
        Config $config
    )
    {
        $this->view = $view;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * @return string
     * Code snippet to display on every page to keep track of users
     */
    public function insertAnalytics()
    {
        //Set the webproperty to be used whether or not universal is used or not
        $data = [
            'webproperty' => $this->config->getWebproperty()
        ];

        //Check to see if universal is set
        if ($this->config->isUniversal()) {
            $data['domainname'] = $this->config->getUniversalDomainname();
            return $this->view->render("universal.twig", $data);
        }

        //Get full url for the current website
        $data['domainname'] = $this->request->getCurrentRequest()->server->get('HTTP_HOST');

        return $this->view->render("normal.twig", $data);
    }
}