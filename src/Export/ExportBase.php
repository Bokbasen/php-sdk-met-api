<?php
namespace Bokbasen\Metadata\Export;

use Bokbasen\Metadata\BaseClient;
use Bokbasen\Auth\Login;
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

    const SUBSCRIPTION_SCHOOL = 'school';

    const SUBSCRIPTION_EXTENDED = 'extended';

    const SUBSCRIPTION_BASIC = 'basic';

    /**
     *
     * @param \Bokbasen\Auth\Login $auth            
     * @param string $baseUrl            
     */
    public function __construct(Login $auth, $url = self::URL_PROD, $subscription = self::SUBSCRIPTION_EXTENDED, HttpClient $httpClient = null)
    {
        parent::__construct($auth, $url, $httpClient);
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
     * @return \Psr\Http\Message\RequestInterface
     */
    protected function createRequest($nextToken, \DateTime $afterDate = null, $pageSize, $path)
    {
        $url = $this->url . $path;
        
        $parameters = []

        ;
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
        
        $request = $this->getMessageFactory()->createRequest('GET', $url, $this->makeHeadersArray($this->auth));
        
        return $request;
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
}