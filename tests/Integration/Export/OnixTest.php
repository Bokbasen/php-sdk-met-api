<?php
namespace Bokbasen\Metadata\Tests\Integration\Export;

class OnixTest extends \Bokbasen\Metadata\Tests\Integration\Base
{

    protected function setUp()
    {
        $config = $this->getConfig();
        $auth = $this->getAuthObject();
        $this->client = new \Bokbasen\Metadata\Export\Onix($this->auth, $config['metApiUrl'], \Bokbasen\Metadata\Export\Onix::SUBSCRIPTION_BASIC);
    }

    public function testOnixByISBN()
    {
        $config = $this->getConfig();
        $onix = $this->client->getByISBN($config['testIsbn']);
        
        $this->assertNotEmpty($onix);
        // verifies valid xml response
        $simpleXml = new \SimpleXMLElement($onix);
    }

    public function testDownloadAfter()
    {
        $config = $this->getConfig();
        $date = new \DateTime();
        $newdate = $date->sub(new \DateInterval('P3M'));
        
        $nextToken = $this->client->downloadAfter($newdate, $config['pathSavedFiles']);
        
        // can we test for download??
    }
}