<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\BaseClient;
use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;
use Http\Client\HttpClient;
use Psr\Http\Message\ResponseInterface;
use Bokbasen\Metadata\Formatters\DownloadFileFormatterInterface;
use Bokbasen\Metadata\Formatters\DefaultDownloadFileFormatter;

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
 * Bokbasen object export SDK, provides a simple interface to download objects from bokbasen object export
 *
 * @link https://bokbasen.jira.com/wiki/display/api/Objects
 * @license https://opensource.org/licenses/MIT
 */
class Object extends BaseClient
{

    const OBJECT_TYPE_AUDIO_SAMPLE = 'ly';

    const OBJECT_COVER_IMAGE_SMALL = 'ol';

    const OBJECT_COVER_IMAGE_LARGE = 'os';

    const OBJECT_COVER_IMAGE_ORIGINAL = 'org';

    const MAX_PAGE_SIZE = 5000;

    /**
     * Last next token returned from the server
     *
     * @var string
     */
    protected $lastNextToken;

    /**
     * Download files based on next parameter, downloads files for the next page in the object report and return true/false depending on if the next report page had data or not
     *
     * @param string $nextToken            
     * @param array $objectsTypesToDownload            
     * @param string $targetPath            
     * @param array $isbnFilter            
     * @param DownloadFileFormatterInterface $filenameFormatter            
     * @param int $pageSize            
     * @return boolean
     */
    public function downloadNext($nextToken, array $objectsTypesToDownload, $targetPath, array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null, $pageSize = self::MAX_PAGE_SIZE)
    {
        $response = $this->httpClient->sendRequest($this->createRequest($nextToken, null, $pageSize));
        
        $status = $this->downloadObjects($response, $objectsTypesToDownload, $targetPath, $isbnFilter, $filenameFormatter);
        
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        return $status;
    }

    /**
     * Download all objects after a certain date, will return next token for last page
     *
     * @param \DateTime $afterDate            
     * @param array $objectsTypesToDownload            
     * @param string $targetPath            
     * @param array $isbnFilter            
     * @param DownloadFileFormatterInterface $filenameFormatter            
     * @param int $pageSize            
     *
     * @return string
     */
    public function downloadAfter(\DateTime $afterDate, array $objectsTypesToDownload, $targetPath, array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null, $pageSize = self::MAX_PAGE_SIZE)
    {
        $response = $this->httpClient->sendRequest($this->createRequest(null, $afterDate, $pageSize));
        $morePages = $this->downloadObjects($response, $objectsTypesToDownload, $targetPath, $isbnFilter, $filenameFormatter);
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        while ($morePages) {
            $morePages = $this->downloadNext($this->lastNextToken, $objectsTypesToDownload, $targetPath, $isbnFilter, $filenameFormatter, $pageSize);
        }
        
        return $this->lastNextToken;
    }

    /**
     * Get the last next token returned from the API
     *
     * @return string
     */
    public function getLastNextToken()
    {
        return $this->lastNextToken;
    }

    /**
     * Save objects to disk based on object report response and given parameters
     *
     * @param ResponseInterface $response            
     * @param array $objectsTypesToDownload            
     * @param string $targetPath            
     * @param array $isbnFilter            
     * @param DownloadFileFormatterInterface $filenameFormatter            
     * @throws BokbasenMetadataAPIException
     *
     * @return bool true if object report had books, false if object report was empty
     */
    protected function downloadObjects(ResponseInterface $response, array $objectsTypesToDownload, $targetPath, array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null)
    {
        $xml = new \SimpleXMLElement((string) $response->getBody());
        
        if (is_null($filenameFormatter)) {
            $filenameFormatter = new DefaultDownloadFileFormatter();
        }
        
        if (count($xml->OBJECT) == 0) {
            return false;
        }
        
        foreach ($xml->OBJECT as $object) {
            
            // skip files based on isbn filter and object types list
            if (! in_array($object->TYPE, $objectsTypesToDownload) || (! empty($isbnFilter) && ! in_array($object->ISBN13, $isbnFilter))) {
                continue;
            }
            
            $targetFilename = $filenameFormatter->getFilename($object);
            $url = (string) $object->REFERENCE;
            
            // @todo this will put entire file into memory, replace with streams
            $request = $this->getMessageFactory()->createRequest('GET', $url, $this->makeHeadersArray($this->auth));
            $response = $this->httpClient->sendRequest($request);
            
            $status = file_put_contents($targetPath . $targetFilename, (string) $response->getBody());
            if ($status === false) {
                throw new BokbasenMetadataAPIException('Could not write file: ' . $targetPath . $targetFilename);
            }
        }
        
        return true;
    }

    /**
     * Create request object for the object report
     *
     * @param string $nextToken            
     * @param \DateTime $afterDate            
     * @param int $pageSize            
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function createRequest($nextToken, \DateTime $afterDate = null, $pageSize)
    {
        $url = $this->url . 'export/object';
        $parameters = [
            'pagesize' => (int) $pageSize
        ];
        
        if (! is_null($nextToken)) {
            $parameters['next'] = $nextToken;
        } elseif (! is_null($afterDate)) {
            $parameters['after'] = $afterDate->format(self::AFTER_PARAMETER_DATE_FORMAT);
        }
        $url .= '?' . http_build_query($parameters);
        $request = $this->getMessageFactory()->createRequest('GET', $url, $this->makeHeadersArray($this->auth));
        
        return $request;
    }
}