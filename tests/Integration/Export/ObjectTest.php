<?php
namespace Bokbasen\Metadata\Tests\Integration\Export;

class ObjectTest extends \Bokbasen\Metadata\Tests\Integration\Base
{

    protected function setUp()
    {
        $config = $this->getConfig();
        $auth = $this->getAuthObject();
        $this->client = new \Bokbasen\Metadata\Export\Object($this->auth, $config['metApiUrl']);
    }

    public function testObjectDownloadAfter()
    {
        $config = $this->getConfig();
        $date = new \DateTime();
        $newdate = $date->sub(new \DateInterval('P3M'));
        
        $this->client->downloadAfter($newdate, $config['pathSavedFiles'], [
            \Bokbasen\Metadata\Export\Object::OBJECT_COVER_IMAGE_SMALL
        ]);
    }
}