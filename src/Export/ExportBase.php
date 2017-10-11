<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\BaseClient;
use Psr\Http\Message\ResponseInterface;
use Bokbasen\ApiClient\Client;

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
 * @license https://opensource.org/licenses/MIT
 */
abstract class ExportBase extends BaseClient
{

    /**
     * Last next token returned from the server
     *
     * @var string
     */
    protected $lastNextToken;

    /**
     *
     * @var string
     */
    protected $subscription;

    public const SUBSCRIPTION_SCHOOL = 'school';

    public const SUBSCRIPTION_EXTENDED = 'extended';

    public const SUBSCRIPTION_BASIC = 'basic';

    /**
     *
     * @param Client $apiClient            
     * @param string $subscription            
     */
    public function __construct(Client $apiClient, $subscription = self::SUBSCRIPTION_EXTENDED)
    {
        parent::__construct($apiClient);
        $this->subscription = $subscription;
    }

    /**
     * Create request object for the object report
     *
     * @param string $nextToken            
     * @param \DateTime $afterDate            
     * @param int $pageSize            
     * @param string $path            
     *
     * @return ResponseInterface
     */
    protected function executeGetRequest($nextToken, \DateTime $afterDate = null, $pageSize, $path): ResponseInterface
    {
        $url = $path;
        
        $parameters = [];
        
        if (! empty($this->subscription)) {
            $parameters['subscription'] = $this->subscription;
        }
        
        if ($pageSize > 0) {
            $parameters['pagesize'] = (int) $pageSize;
        }
        
        if (! is_null($nextToken)) {
            $parameters['next'] = $nextToken;
        } elseif (! is_null($afterDate)) {
            $parameters['after'] = $afterDate->format(self::AFTER_PARAMETER_DATE_FORMAT);
        }
        if (! empty($parameters)) {
            $url .= '?' . http_build_query($parameters);
        }
        
        return $this->apiClient->get($url, null);
    }

    /**
     * Get the last next token returned from the API
     *
     * @return string
     */
    public function getLastNextToken():?string
    {
        return $this->lastNextToken;
    }
}