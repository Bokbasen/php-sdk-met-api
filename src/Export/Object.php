<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;
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
class Object extends ExportBase
{

    public const OBJECT_TYPE_AUDIO_SAMPLE = 'ly';

    public const OBJECT_COVER_IMAGE_SMALL = 'ol';

    public const OBJECT_COVER_IMAGE_LARGE = 'os';

    public const OBJECT_COVER_IMAGE_ORIGINAL = 'org';

    public const MAX_PAGE_SIZE = 5000;

    protected const PATH = 'export/object';

    /**
     *
     * @var array
     */
    protected $downloadedIsbns = [];

    /**
     * Download files based on next parameter, downloads files for the next page in the object report and return true/false depending on if the next report page had data or not
     *
     * @param string $nextToken            
     * @param string $targetPath            
     * @param array $objectsTypesToDownload            
     * @param array $isbnFilter            
     * @param DownloadFileFormatterInterface $filenameFormatter            
     * @param int $pageSize            
     * @return boolean
     */
    public function downloadNext(string $nextToken, string $targetPath, array $objectsTypesToDownload = [], array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null, int $pageSize = self::MAX_PAGE_SIZE): bool
    {
        $response = $this->executeGetRequest($nextToken, null, $pageSize, self::PATH);
        
        $status = $this->downloadObjects($response, $objectsTypesToDownload, $targetPath, $isbnFilter, $filenameFormatter);
        
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        return $status;
    }

    /**
     * Download all objects after a certain date, will return next token for last page
     *
     * @param \DateTime $afterDate            
     * @param string $targetPath            
     * @param array $objectsTypesToDownload            
     * @param array $isbnFilter            
     * @param DownloadFileFormatterInterface $filenameFormatter            
     * @param int $pageSize            
     *
     * @return string
     */
    public function downloadAfter(\DateTime $afterDate, string $targetPath, array $objectsTypesToDownload = [], bool $downloadAllPages = true, array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null, int $pageSize = self::MAX_PAGE_SIZE): string
    {
        $response = $this->executeGetRequest(null, $afterDate, $pageSize, self::PATH);
        $morePages = $this->downloadObjects($response, $objectsTypesToDownload, $targetPath, $isbnFilter, $filenameFormatter);
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        if ($morePages && $downloadAllPages) {
            while ($morePages) {
                $morePages = $this->downloadNext($this->lastNextToken, $targetPath, $objectsTypesToDownload, $isbnFilter, $filenameFormatter, $pageSize);
                
                if (! empty($isbnFilter) && count($isbnFilter) == count($this->downloadedIsbns)) {
                    $morePages = false;
                }
            }
        }
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
    protected function downloadObjects(ResponseInterface $response, array $objectsTypesToDownload, string $targetPath, array $isbnFilter = [], DownloadFileFormatterInterface $filenameFormatter = null): bool
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
            if ((! empty($objectsTypesToDownload) && ! in_array($object->TYPE, $objectsTypesToDownload)) || (! empty($isbnFilter) && ! in_array($object->ISBN13, $isbnFilter))) {
                continue;
            }
            
            $targetFilename = $filenameFormatter->getFilename($object);
            $url = (string) $object->REFERENCE;
            
            $response = $this->apiClient->executeHttpRequest('GET', [], null, $url);
            
            $status = file_put_contents($targetPath . $targetFilename, (string) $response->getBody());
            if ($status === false) {
                throw new BokbasenMetadataAPIException('Could not write file: ' . $targetPath . $targetFilename);
            }
            $this->downloadedIsbns[] = $object->ISBN13;
        }
        
        return true;
    }
}