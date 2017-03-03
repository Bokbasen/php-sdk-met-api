<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\Exceptions\BokbasenMetadataAPIException;
use Http\Client\HttpClient;
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

    const MAX_PAGE_SIZE = 1000;

    const PATH = 'export/onix';

    public function getByISBN($isbn)
    {
        $subscription = $this->subscription;
        $this->subscription = null;
        $response = $this->httpClient->sendRequest($this->createRequest(null, null, null, self::PATH . '/' . $isbn));
        $this->subscription = $subscription;
        
        return (string) $response->getBody();
    }

    public function downloadNext($nextToken, $targetFolder, $pageSize = self::MAX_PAGE_SIZE)
    {
        $response = $this->httpClient->sendRequest($this->createRequest($nextToken, null, $pageSize, self::PATH));
        
        $this->saveOnixToDisk($response, $targetFolder);
        
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        return $response->hasHeader('Link');
    }

    public function downloadAfter(\DateTime $afterDate, $targetFilename, $downloadAllPages = true, $pageSize = self::MAX_PAGE_SIZE)
    {
        $response = $this->httpClient->sendRequest($this->createRequest(null, $afterDate, $pageSize, self::PATH));
        $this->saveOnixToDisk($response, $targetFilename);
        $morePages = $response->hasHeader('Link');
        $this->lastNextToken = $response->getHeaderLine('Next');
        
        if ($downloadAllPages && $morePages) {
            while ($morePages) {
                $morePages = $this->downloadNext($this->lastNextToken, $targetFilename, $pageSize);
            }
        }
        
        return $this->lastNextToken;
    }

    protected function makeFilename($folder)
    {
        $filename = microtime(true) . '-onix';
        
        return $folder . $filename . '.xml';
    }

    protected function saveOnixToDisk(ResponseInterface $response, $targetFolder)
    {
        $targetFilename = $this->makeFilename($targetFolder);
        
        $status = file_put_contents($targetFilename, (string) $response->getBody(), FILE_APPEND);
        if ($status === false) {
            throw new BokbasenMetadataAPIException('Could not write file: ' . $targetFilename);
        }
    }
}