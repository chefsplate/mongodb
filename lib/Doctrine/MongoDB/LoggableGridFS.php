<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Traits\LoggableCollectionTrait;

class LoggableGridFS extends GridFS implements Loggable
{
    use LoggableCollectionTrait;

    /**
     * @param Database $database Database to which this collection belongs
     * @param \MongoGridFS $mongoGridFS MongoGridFS instance being wrapped
     * @param EventManager $evm EventManager instance
     * @param Configuration $configuration
     * @param callable $loggerCallable The logger callable
     */
    public function __construct(
        Database $database,
        \MongoGridFS $mongoGridFS,
        EventManager $evm,
        Configuration $configuration,
        $loggerCallable = null
    ) {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }

        parent::__construct($database, $mongoGridFS, $evm, $configuration);

        $this->loggerCallable = $loggerCallable;
    }

    /*
     * @see GridFS::storeFile()
     */
    public function storeFile($file, array &$document, array $options = [])
    {
        $this->log([
            'storeFile' => true,
            'count' => count($document),
            'options' => $options,
        ]);

        return parent::storeFile($file, $document, $options);
    }
}
