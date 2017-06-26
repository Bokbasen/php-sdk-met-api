<?php
namespace Bokbasen\Metadata\Tests\Unit;

use Bokbasen\Auth\Login;
use Http\Mock\Client;
use Bokbasen\ApiClient\Client as ApiClient;

class ObjectImportTest extends \PHPUnit\Framework\TestCase
{

    protected function createApiClientObject()
    {
        // Create mock of auth object
        $authClientMock = new Client();
        
        $response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $tgt = 'TGT-152-leeshOABMDJE41s55z9WBLq7d7kk2ONUQozYHOF2FimxI5a9D9Z-login.boknett.no';
        $response->method('getHeaderLine')->willReturn($tgt);
        $response->method('getStatusCode')->willReturn('201');
        
        $authClientMock->addResponse($response);
        $mockedAuth = $this->getMockBuilder('Bokbasen\Auth\Login')
            ->setConstructorArgs([
            'username',
            'password',
            null,
            null,
            null,
            $authClientMock
        ])
            ->getMock();
        
        $mockedAuth->method('getAuthHeadersAsArray')->willReturn([
            'Authorization' => 'Boknett TGT-7410-3tRUd04NDPjrhfqqDC26OaINjujwf6njbq2QbyVGpG1HEhNqPA-login.boknett.webbe.no',
            'Date' => gmdate('D, d M Y H:i:s e')
        ]);
        
        return new ApiClient($mockedAuth,'https://www.api.boknett.no');
    }

    public function testImport()
    {
        $apiClient = $this->createApiClientObject();
        
        // Create mock client for order SDK
        $client = new Client();
        
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $mockedResponse->method('getStatusCode')->willReturn('201');
        
        $client->addResponse($mockedResponse);
        $apiClient->setHttpClient($client);
        
        $objectImportSdk = new \Bokbasen\Metadata\Import\Object($apiClient);
        $objectImportSdk->importObjectData('someData', '97888844545', \Bokbasen\Metadata\Import\Object::TYPE_PRODUCT_IMAGE);
        // No exception thrown, all ok
    }

    public function testFailedImport()
    {
        $apiClient = $this->createApiClientObject();
        $this->expectException(\Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException::class);
        // Create mock client for order SDK
        $client = new Client();
        
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $mockedResponse->method('getStatusCode')->willReturn('400');
        
        $client->addResponse($mockedResponse);
        $apiClient->setHttpClient($client);
        $objectImportSdk = new \Bokbasen\Metadata\Import\Object($apiClient);
        $objectImportSdk->importObjectData('someData', '97888844545', \Bokbasen\Metadata\Import\Object::TYPE_PRODUCT_IMAGE);
    }

    public function testFileDoesNotExistInput()
    {
        $apiClient = $this->createApiClientObject();
        $this->expectException(\Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException::class);
        // Create mock client for order SDK
        $client = new Client();
        
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $mockedResponse->method('getStatusCode')->willReturn('400');
        
        $client->addResponse($mockedResponse);
        $apiClient->setHttpClient($client);
        $objectImportSdk = new \Bokbasen\Metadata\Import\Object($apiClient);
        $objectImportSdk->importFromPath('fileThatDoesNotExists.jpg', '97888844545', \Bokbasen\Metadata\Import\Object::TYPE_PRODUCT_IMAGE);
    }
}