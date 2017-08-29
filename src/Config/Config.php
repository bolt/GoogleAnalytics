<?php

namespace Bolt\Extension\Bolt\GoogleAnalytics\Config;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Config class that holds the configuration of the extension.
 */
class Config extends ParameterBag
{
    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * @return string
     */
    public function getWebProperty()
    {
        return $this->get('webproperty');
    }

    /**
     * @param string $webProperty
     *
     * @return Config
     */
    public function setWebProperty($webProperty)
    {
        $this->set('webproperty', $webProperty);

        return $this;
    }

    /**
     * @return bool
     */
    public function isUniversal()
    {
        return $this->getBoolean('universal');
    }

    /**
     * @param bool $universal
     *
     * @return Config
     */
    public function setUniversal($universal)
    {
        $this->set('universal', (bool) $universal);

        return $this;
    }

    /**
     * @return string
     */
    public function getUniversalDomainName()
    {
        return $this->get('universal_domainname');
    }

    /**
     * @param string $universalDomainName
     *
     * @return Config
     */
    public function setUniversalDomainName($universalDomainName)
    {
        $this->set('universal_domainname', $universalDomainName);

        return $this;
    }

    /**
     * @return bool
     */
    public function isBackend()
    {
        return $this->getBoolean('backend');
    }

    /**
     * @param bool $backend
     *
     * @return Config
     */
    public function setBackend($backend)
    {
        $this->set('backend', (bool) $backend);

        return $this;
    }

    /**
     * @return bool
     */
    public function isWidget()
    {
        return $this->getBoolean('widget');
    }

    /**
     * @param bool $widget
     *
     * @return Config
     */
    public function setWidget($widget)
    {
        $this->set('widget', (bool) $widget);

        return $this;
    }

    /**
     * @return string
     */
    public function getGaProfileId()
    {
        return $this->get('ga_profile_id');
    }

    /**
     * @param mixed $gaProfileId
     *
     * @return Config
     */
    public function setGaProfileId($gaProfileId)
    {
        $this->set('ga_profile_id', $gaProfileId);

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyFile()
    {
        return $this->get('key_file');
    }

    /**
     * @param string $keyFile
     *
     * @return Config
     */
    public function setKeyFile($keyFile)
    {
        $this->set('key_file', $keyFile);

        return $this;
    }

    /**
     * @return string
     */
    public function getServiceAccountEmail()
    {
        return $this->get('service_account_email');
    }

    /**
     * @param string $serviceAccountEmail
     *
     * @return Config
     */
    public function setServiceAccountEmail($serviceAccountEmail)
    {
        $this->set('service_account_email', $serviceAccountEmail);

        return $this;
    }
}
