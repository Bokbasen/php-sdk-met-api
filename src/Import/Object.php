<?php
namespace Bokbasen\Metadata\Import;

use \Psr\Http\Message\ResponseInterface;
use Bokbasen\Metadata\BaseClient;
use Bokbasen\ApiClient\HttpRequestOptions;
use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;

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

    public const TYPE_PRODUCT_IMAGE = 'productimage';

    public const TYPE_AUDIO_SAMPLE = 'audiosample';

    public const TYPE_TABLE_OF_CONTENT = 'tableofcontents';

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
     *
     * @return ResponseInterface
     */
    public function importObjectData($fileContent, string $isbn, string $type, ?string $productOwnerId = null): ResponseInterface
    {
        if (is_null($productOwnerId)) {
            $relativePath = sprintf('/import/object/%s/%s/', $isbn, $type);
        } else {
            $relativePath = sprintf('/import/object/%s/%s/%s/', $isbn, $productOwnerId, $type);
        }
        
        $response = $this->apiClient->post($relativePath, $fileContent, HttpRequestOptions::CONTENT_TYPE_JPEG);
        
        if ($response->getStatusCode() != 201) {
            throw new BokbasenMetadataAPIException('Import failed for ISBN ' . $isbn . 'Error from server: ' . (string) $response->getBody());
        }
        
        return $response;
    }

    /**
     * Same as importObjectData except that you can specify a path to the file to import
     *
     *
     * @param string $pathToFile            
     * @param string $isbn            
     * @param string $type            
     * @param string $productOwnerId            
     *
     * @return ResponseInterface
     */
    public function importFromPath(string $pathToFile, string $isbn, string $type, ?string $productOwnerId = null): ResponseInterface
    {
        $resource = @fopen($pathToFile, 'r');
        
        if ($resource === false) {
            throw new BokbasenMetadataAPIException('could not open: ' . $pathToFile);
        }
        return $this->importObjectData($resource, $isbn, $type, $productOwnerId);
    }
}