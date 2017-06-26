<?php
namespace Bokbasen\Metadata\Formatters;

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
 * Default file name formatter used for downloading objects if no formatter is injected to the \Export\Object class
 *
 * @license https://opensource.org/licenses/MIT
 */
class DefaultDownloadFileFormatter implements DownloadFileFormatterInterface
{

    public function getFilename(\SimpleXMLElement $objectReportXml):string
    {
        if (isset($objectReportXml->ISBN13) && ! empty($objectReportXml->ISBN13)) {
            $ean = $objectReportXml->ISBN13;
        } else {
            $ean = $objectReportXml->EAN;
        }
        return $ean . '-' . $objectReportXml->TYPE . $this->getFileExtension($objectReportXml->TYPE);
    }

    protected function getFileExtension($type):string
    {
        if ($type == \Bokbasen\Metadata\Export\Object::OBJECT_TYPE_AUDIO_SAMPLE) {
            $ext = '.mp3';
        } else {
            $ext = '.jpg';
        }
        return $ext;
    }
}