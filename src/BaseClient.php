<?php
namespace Bokbasen\Metadata;

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
 * Shared class for all Metadata SDK clients
 *
 * @license https://opensource.org/licenses/MIT
 */
abstract class BaseClient
{
    use \Bokbasen\Http\HttpMethodsTrait;

    const URL_PROD = 'https://api.boknett.no/metadata/';

    const URL_TEST = 'https://api.boknett.webbe.no/metadata/';

    /**
     *
     * @param \Bokbasen\Auth\Login $auth            
     * @param string $baseUrl            
     */
    public function __construct(Login $auth, $url = self::URL_PROD, HttpClient $httpClient = null)
    {
        $this->auth = $auth;
        $this->url = $url;
        $this->setHttpClient($httpClient);
    }
}