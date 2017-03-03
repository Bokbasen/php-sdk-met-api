<?php
namespace Bokbasen\Metadata\Tests\Integration;

abstract class Base extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * @var \Bokbasen\Auth\Login
     */
    protected $auth;

    /**
     *
     * @var \Bokbasen\Metadata\Export\BaseClient
     */
    protected $client;

    protected function getAuthObject()
    {
        $config = $this->getConfig();
        if (is_null($this->auth)) {
            $this->auth = new \Bokbasen\Auth\Login($config['username'], $config['password'], null, $config['loginUrl']);
        }
        return $this->auth;
    }

    protected function getConfig()
    {
        if (empty($this->config)) {
            $this->config = parse_ini_file(__DIR__ . '/config.ini');
        }
        return $this->config;
    }
}