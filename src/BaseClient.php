<?php
namespace Bokbasen\Metadata;

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
 * Shared class for all Metadata SDK clients
 *
 * @license https://opensource.org/licenses/MIT
 */
abstract class BaseClient
{

    const URL_PROD = 'https://api.boknett.no/metadata/';

    const URL_TEST = 'https://api.boknett.webbe.no/metadata/';

    const URL_DEV = 'https://api.boknett.dev.webbe.no/metadata/';

    const AFTER_PARAMETER_DATE_FORMAT = 'YmdHis';

    /**
     *
     * @var Client
     */
    protected $apiClient;

    /**
     *
     * @param Client $auth            
     */
    public function __construct(Client $apiClient)
    {
        $this->apiClient = $apiClient;
    }
}