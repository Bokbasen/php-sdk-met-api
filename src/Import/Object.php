<?php
namespace Bokbasen\Metadata\Import;

use Bokbasen\Metadata\BaseClient;
use Bokbasen\Http\HttpRequestOptions;
use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;
use Http\Client\HttpClient;

/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
/**
 * Bokbasen object import SDK
 *
 * @link https://bokbasen.jira.com/wiki/display/api/Import+Service#ImportService-ObjectImport
 * @license https://opensource.org/licenses/MIT
 */
class Object extends BaseClient
{

    const TYPE_PRODUCT_IMAGE = 'productimage';

    const TYPE_AUDIO_SAMPLE = 'audiosample';

    const TYPE_TABLE_OF_CONTENT = 'tableofcontents';

    const TYPE_CONTENT_MAPPING = [
        self::TYPE_AUDIO_SAMPLE => HttpRequestOptions::CONTENT_TYPE_AUDIO_MPEG,
        self::TYPE_PRODUCT_IMAGE => HttpRequestOptions::CONTENT_TYPE_JPEG,
        self::TYPE_TABLE_OF_CONTENT => HttpRequestOptions::CONTENT_TYPE_PDF
    ];

    /**
     * Import object based on binary data (as string or stream), $productOwnerId is in general not needed and you need special API permissions to use this parameter
     *
     *
     * @param StreamInterface|resource|string $fileContent            
     * @param string $isbn            
     * @param string $type            
     * @param string $productOwnerId            
     * @throws BokbasenMetadataAPIException
     */
    public function importObjectData($fileContent, $isbn, $type, $productOwnerId = null)
    {
        if (is_null($productOwnerId)) {
            $url = sprintf($this->url . 'import/object/%s/%s/', $isbn, $type);
        } else {
            $url = sprintf($this->url . 'import/object/%s/%s/%s/', $isbn, $productOwnerId, $type);
        }
        
        $request = $this->getMessageFactory()->createRequest('POST', $url, $this->makeObjectImportHeader($type), $fileContent);
        
        $response = $this->httpClient->sendRequest($request);
        
        if ($this->needReAuthentication($response)) {
            $this->importObjectData($fileContent, $isbn, $type, $productOwnerId);
        } elseif ($response->getStatusCode() != 201) {
            throw new BokbasenMetadataAPIException('Import failed for ISBN ' . $isbn . 'Error from server: ' . (string) $response->getBody());
        }
    }

    /**
     * Same as importObjectData except that you can specify a path to the file to import
     *
     *      
     * @param string $pathToFile            
     * @param string $isbn            
     * @param string $type            
     * @param string $productOwnerId            
     */
    public function importFromPath($pathToFile, $isbn, $type, $productOwnerId = null)
    {
        $resource = @fopen($pathToFile, 'r');
        
        if ($resource === false) {
            throw new BokbasenMetadataAPIException('could not open: ' . $pathToFile);
        }
        return $this->importObjectData($resource, $isbn, $type, $productOwnerId);
    }

    /**
     * Make HTTP headers for object import
     *
     * @param string $type            
     * @throws BokbasenMetadataAPIException
     * @return array
     */
    protected function makeObjectImportHeader($type)
    {
        if (! isset(self::TYPE_CONTENT_MAPPING[$type])) {
            throw new BokbasenMetadataAPIException('Invalid type provided: ' . $type);
        } else {
            $contentType = self::TYPE_CONTENT_MAPPING[$type];
        }
        
        return $this->makeHeadersArray($this->auth, [
            'Content-type' => $contentType
        ]);
    }
}