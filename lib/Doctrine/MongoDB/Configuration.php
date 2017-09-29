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

/**
 * Configuration class for creating a Connection.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Configuration
{
    /**
     * @deprecated
     * @var string
     */
    protected $mongoCmd = '$';

    /** @var int */
    protected $numRetryConnect = 0;

    /** @var int */
    protected $numRetryReads = 0;

    /** @var int */
    protected $timeBetweenReadRetriesMs = 0;

    /** @var int */
    protected $numRetryWrites = 0;

    /** @var int */
    protected $timeBetweenWriteRetriesMs = 0;

    /** @var callable */
    protected $loggerCallable;

    /**
     * Gets the logger callable.
     *
     * @return callable|null
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * Set the logger callable.
     *
     * @param callable $loggerCallable
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Get the MongoDB command prefix.
     *
     * @deprecated 1.1 No longer supported; will be removed for 1.2
     * @return string
     */
    public function getMongoCmd()
    {
        trigger_error('MongoDB command prefix option is no longer used', E_USER_DEPRECATED);
        return $this->mongoCmd;
    }

    /**
     * Set the MongoDB command prefix.
     *
     * @deprecated 1.1 No longer supported; will be removed for 1.2
     * @param string $cmd
     */
    public function setMongoCmd($cmd)
    {
        trigger_error('MongoDB command prefix option is no longer used', E_USER_DEPRECATED);
        $this->mongoCmd = $cmd;
    }

    /**
     * Get the number of times to retry connection attempts after an exception.
     *
     * @return integer
     */
    public function getNumRetryConnect()
    {
        return $this->numRetryConnect;
    }

    /**
     * Set the number of times to retry connection attempts after an exception.
     *
     * @param boolean|integer $numRetryConnect
     */
    public function setNumRetryConnect($numRetryConnect)
    {
        $this->numRetryConnect = (integer) $numRetryConnect;
    }

    /**
     * Get the number of times to retry read queries after an exception.
     *
     * @return integer
     */
    public function getNumRetryReads()
    {
        return $this->numRetryReads;
    }

    /**
     * Set the number of times to retry read queries after an exception.
     *
     * @param boolean|integer $numRetryReads
     */
    public function setNumRetryReads($numRetryReads)
    {
        $this->numRetryReads = (integer) $numRetryReads;
    }

    /**
     * @return int
     */
    public function getTimeBetweenReadRetriesMs()
    {
        return $this->timeBetweenReadRetriesMs;
    }

    /**
     * @param int $timeBetweenReadRetriesMs
     */
    public function setTimeBetweenReadRetriesMs($timeBetweenReadRetriesMs)
    {
        $this->timeBetweenReadRetriesMs = (integer) $timeBetweenReadRetriesMs;
    }

    /**
     * Get the number of times to retry write queries after an exception.
     *
     * @return int
     */
    public function getNumRetryWrites()
    {
        return $this->numRetryWrites;
    }

    /**
     * Get the number of times to retry write queries after an exception.
     *
     * @param boolean|integer $numRetryWrites
     */
    public function setNumRetryWrites($numRetryWrites)
    {
        $this->numRetryWrites = (integer) $numRetryWrites;
    }

    /**
     * @return int
     */
    public function getTimeBetweenWriteRetriesMs()
    {
        return $this->timeBetweenWriteRetriesMs;
    }

    /**
     * @param int $timeBetweenWriteRetriesMs
     */
    public function setTimeBetweenWriteRetriesMs($timeBetweenWriteRetriesMs)
    {
        $this->timeBetweenWriteRetriesMs = (integer) $timeBetweenWriteRetriesMs;
    }
}
