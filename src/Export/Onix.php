<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;
use Psr\Http\Message\ResponseInterface;

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
 * Bokbasen object export SDK, provides a simple interface to download ONIX from bokbasen export
 *
 * @link https://bokbasen.jira.com/wiki/spaces/api/pages/67993632/ONIX
 * @license https://opensource.org/licenses/MIT
 */
class Onix extends ExportBase
{

    public const MAX_PAGE_SIZE = 1000;

    protected const PATH = 'metadata/export/onix';

    /**
     * Get ONIX by ISBN ,returns XML as string
     *
     * @param string $isbn            
     * @return string
     */
    public function getByISBN(string $isbn): string
    {
        $subscription = $this->subscription;
        $this->subscription = null;
        $response = $this->apiClient->get(self::PATH . '/' . $isbn);
        $this->subscription = $subscription;
        
        return (string) $response->getBody();
    }

    /**
     * Execute metadata request with next-token, returns response object
     *
     * @param string $nextToken            
     * @param int $pageSize            
     * @return ResponseInterface
     */
    public function getNext(string $nextToken, int $pageSize = self::MAX_PAGE_SIZE): ResponseInterface
    {
        $response = $this->executeGetRequest($nextToken, null, $pageSize, self::PATH);
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        return $response;
    }

    /**
     * Execute metadata request with after parameter, returns response object
     *
     * @param \DateTime $afterDate            
     * @param int $pageSize            
     * @return ResponseInterface
     */
    public function getAfter(\DateTime $afterDate, int $pageSize = self::MAX_PAGE_SIZE): ResponseInterface
    {
        $response = $this->executeGetRequest(null, $afterDate, $pageSize, self::PATH);
        $this->lastNextToken = $response->getHeaderLine('Next');
        return $response;
    }

    /**
     * Download XML to file based on next token, returns true if there are more pages to iterate over
     *
     * @param string $nextToken            
     * @param string $targetFolder            
     * @param int $pageSize            
     * @return bool
     */
    public function downloadNext(string $nextToken, string $targetFolder, int $pageSize = self::MAX_PAGE_SIZE): bool
    {
        $response = $this->getNext($nextToken, $pageSize);
        
        $this->saveOnixToDisk($response, $targetFolder);
        
        return $response->hasHeader('Link');
    }

    /**
     * Download XML to file based on after DateTime, returns token to use with downloadNext to iterate over further pages.
     *
     * @param \DateTime $afterDate            
     * @param string $targetFilename            
     * @param bool $downloadAllPages            
     * @param int $pageSize            
     * @return string
     */
    public function downloadAfter(\DateTime $afterDate, string $targetFilename, bool $downloadAllPages = true, int $pageSize = self::MAX_PAGE_SIZE): string
    {
        $response = $this->getAfter($afterDate, $pageSize);
        $this->saveOnixToDisk($response, $targetFilename);
        $morePages = $response->hasHeader('Link');
        
        if ($downloadAllPages && $morePages) {
            while ($morePages) {
                $morePages = $this->downloadNext($this->lastNextToken, $targetFilename, $pageSize);
            }
        }
        
        return $this->lastNextToken;
    }

    protected function makeFilename(string $folder): string
    {
        $filename = microtime(true) . '-onix';
        
        return $folder . $filename . '.xml';
    }

    protected function saveOnixToDisk(ResponseInterface $response, string $targetFolder): void
    {
        $targetFilename = $this->makeFilename($targetFolder);
        
        $status = file_put_contents($targetFilename, (string) $response->getBody(), FILE_APPEND);
        if ($status === false) {
            throw new BokbasenMetadataAPIException('Could not write file: ' . $targetFilename);
        }
    }
}