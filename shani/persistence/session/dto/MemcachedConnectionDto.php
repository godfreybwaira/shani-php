<?php

/**
 * Description of MemcachedConnectionDto
 * @author goddy
 *
 * Created on: Apr 6, 2026 at 10:58:39 AM
 */

namespace shani\persistence\session\dto {

    use shani\persistence\session\SessionConnectionInterface;

    /**
     *  Handle session using memcached. Requires memcached and php-memcached extension to be installed.
     */
    final class MemcachedConnectionDto implements SessionConnectionInterface
    {

        /**
         * Host connecting address or name.
         * @var string
         */
        public readonly string $hostname;

        /**
         * Host connecting port.
         * @var int
         */
        public readonly int $port;

        /**
         * Connection timeout. Default is 3 seconds
         * @var int
         */
        public readonly int $timeout;

        /**
         * Reuse same connection. Default is true
         * @var bool
         */
        public readonly bool $persistent;

        /**
         *
         * @param string $hostname Host connecting address or name.
         * @param int $port Host connecting port.
         * @param int $timeout Connection timeout. Default is 3 seconds
         * @param bool $persistent Reuse same connection. Default is true
         */
        public function __construct(string $hostname, int $port, int $timeout = 3, bool $persistent = true)
        {
            $this->hostname = $hostname;
            $this->port = $port;
            $this->timeout = $timeout;
            $this->persistent = $persistent;
        }

        public function getConnectionString(): string
        {
            $query = http_build_query([
                'timeout' => $this->timeout,
                'persistent' => (bool) $this->persistent
            ]);
            return $this->hostname . ':' . $this->port . '?' . $query;
        }

        public function getHandler(): string
        {
            return 'memcached';
        }
    }

}
