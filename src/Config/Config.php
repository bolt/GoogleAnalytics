<?php


namespace Bolt\Extension\Bolt\GoogleAnalytics\Config;

/**
 * Class Config
 * @package Bolt\Extension\Bolt\GoogleAnalytics\Config
 * Config class that holds the configuration of the extension
 */
class Config
{

    private $webproperty;

    private $universal;

    private $universalDomainname;

    private $backend;

    private $gaProfileId;

    private $keyFile;

    private $serviceAccountEmail;

    /**
     * Config constructor.
     * @param $config
     * Setup the configuration from the config array
     */
    public function __construct($config)
    {
        $this->setWebproperty($config['webproperty']);
        $this->setUniversal($config['universal']);
        $this->setUniversalDomainname($config['universal_domainname']);
        $this->setBackend($config['backend']);
        $this->setGaProfileId($config['ga_profile_id']);
        $this->setKeyFile($config['key_file']);
        $this->setServiceAccountEmail($config['service_account_email']);
    }

    /**
     * @return mixed
     */
    public function getWebproperty()
    {
        return $this->webproperty;
    }

    /**
     * @param mixed $webproperty
     * @return Config
     */
    public function setWebproperty($webproperty)
    {
        $this->webproperty = $webproperty;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isUniversal()
    {
        return $this->universal;
    }

    /**
     * @param mixed $universal
     * @return Config
     */
    public function setUniversal($universal)
    {
        $this->universal = $universal;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUniversalDomainname()
    {
        return $this->universalDomainname;
    }

    /**
     * @param mixed $universalDomainname
     * @return Config
     */
    public function setUniversalDomainname($universalDomainname)
    {
        $this->universalDomainname = $universalDomainname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function isBackend()
    {
        return $this->backend;
    }

    /**
     * @param mixed $backend
     * @return Config
     */
    public function setBackend($backend)
    {
        $this->backend = $backend;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGaProfileId()
    {
        return $this->gaProfileId;
    }

    /**
     * @param mixed $gaProfileId
     * @return Config
     */
    public function setGaProfileId($gaProfileId)
    {
        $this->gaProfileId = $gaProfileId;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getKeyFile()
    {
        return $this->keyFile;
    }

    /**
     * @param mixed $keyFile
     * @return Config
     */
    public function setKeyFile($keyFile)
    {
        $this->keyFile = $keyFile;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceAccountEmail()
    {
        return $this->serviceAccountEmail;
    }

    /**
     * @param mixed $serviceAccountEmail
     * @return Config
     */
    public function setServiceAccountEmail($serviceAccountEmail)
    {
        $this->serviceAccountEmail = $serviceAccountEmail;
        return $this;
    }
}