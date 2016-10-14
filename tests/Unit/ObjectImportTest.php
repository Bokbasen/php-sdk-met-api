<?php
namespace Bokbasen\Metadata\Tests\Unit;

use Bokbasen\Auth\Login;
use Http\Mock\Client;

class ObjectImportTest extends \PHPUnit_Framework_TestCase
{

    protected function createMockAuthObject()
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
            $authClientMock
        ])
            ->getMock();
        
        $mockedAuth->method('getAuthHeadersAsArray')->willReturn([
            'Authorization' => 'Boknett TGT-7410-3tRUd04NDPjrhfqqDC26OaINjujwf6njbq2QbyVGpG1HEhNqPA-login.boknett.webbe.no',
            'Date' => gmdate('D, d M Y H:i:s e')
        ]);
        
        return $mockedAuth;
    }

    public function testImport()
    {
        $authMoch = $this->createMockAuthObject();
        
        // Create mock client for order SDK
        $client = new Client();
        
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $mockedResponse->method('getStatusCode')->willReturn('201');
        
        $client->addResponse($mockedResponse);
        
        $objectImportSdk = new \Bokbasen\Metadata\ObjectImport($authMoch, null, $client);
        $objectImportSdk->importObjectData('someData', '97888844545', \Bokbasen\Metadata\ObjectImport::TYPE_PRODUCT_IMAGE);
        // No exception thrown, all ok
    }

    public function testFailedImport()
    {
        $authMoch = $this->createMockAuthObject();
        $this->expectException(\Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException::class);
        // Create mock client for order SDK
        $client = new Client();
        
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        
        $mockedResponse->method('getStatusCode')->willReturn('400');
        
        $client->addResponse($mockedResponse);
        
        $objectImportSdk = new \Bokbasen\Metadata\ObjectImport($authMoch, null, $client);
        $objectImportSdk->importObjectData('someData', '97888844545', \Bokbasen\Metadata\ObjectImport::TYPE_PRODUCT_IMAGE);
    }
    
    public function testFileDoesNotExistInput()
    {
        $authMoch = $this->createMockAuthObject();
        $this->expectException(\Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException::class);
        // Create mock client for order SDK
        $client = new Client();
    
        $mockedResponse = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
    
        $mockedResponse->method('getStatusCode')->willReturn('400');
    
        $client->addResponse($mockedResponse);
    
        $objectImportSdk = new \Bokbasen\Metadata\ObjectImport($authMoch, null, $client);
        $objectImportSdk->importFromPath('fileThatDoesNotExists.jpg', '97888844545', \Bokbasen\Metadata\ObjectImport::TYPE_PRODUCT_IMAGE);
    }
}