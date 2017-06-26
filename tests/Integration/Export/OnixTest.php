<?php
namespace Bokbasen\Metadata\Tests\Integration\Export;
use Bokbasen\ApiClient\Client as ApiClient;
class OnixTest extends \Bokbasen\Metadata\Tests\Integration\Base
{

    protected function setUp()
    {
        $config = $this->getConfig();
        $auth = $this->getAuthObject();
        $apiClient = new ApiClient($auth, $config['metApiUrl']);
        $this->client = new \Bokbasen\Metadata\Export\Onix($apiClient, \Bokbasen\Metadata\Export\Onix::SUBSCRIPTION_BASIC);
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