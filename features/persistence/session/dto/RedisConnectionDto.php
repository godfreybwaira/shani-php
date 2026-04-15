<?php

/**
 * Description of RedisConnectionDto
 * @author goddy
 *
 * Created on: Apr 6, 2026 at 11:00:56 AM
 */

namespace features\persistence\session\dto {

    use features\persistence\session\SessionConnectionInterface;

    /**
     * Handle session using redis Requires redis and php-redis extension to be installed.
     */
    final class RedisConnectionDto implements SessionConnectionInterface
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
         * Host password. Default is empty.
         * @var string|null
         */
        public readonly ?string $password;

        /**
         * Prefix
         * @var string
         */
        public readonly string $prefix;

        /**
         * Host protocol. Default is "tcp".
         * @var string
         */
        public readonly string $protocol;

        /**
         * Default database to connect to. Default is 0
         * @var int
         */
        public readonly int $database;

        /**
         * Connection timeout. Default is 3 seconds.
         * @var int
         */
        public readonly int $timeout;

        /**
         *
         * @param string $hostname Host connecting address or name.
         * @param int $port Host connecting port.
         * @param string|null $password Host password. Default is empty.
         * @param int $timeout Connection timeout. Default is 3 seconds.
         * @param string $prefix Prefix
         * @param string $protocol Host protocol. Default is "tcp".
         * @param int $database Default database to connect to. Default is 0
         */
        public function __construct(string $hostname, int $port, ?string $password = null, int $timeout = 3, string $prefix = 'sess_', string $protocol = 'tcp', int $database = 0)
        {
            $this->hostname = $hostname;
            $this->port = $port;
            $this->password = $password;
            $this->prefix = $prefix;
            $this->protocol = $protocol;
            $this->database = $database;
            $this->timeout = $timeout;
        }

        public function getConnectionString(): string
        {
            $data = [
                'database' => $this->database,
                'prefix' => $this->prefix,
                'timeout' => $this->timeout,
            ];
            if ($this->password !== null) {
                $data['auth'] = $this->password;
            }
            return $this->protocol . '://' . $this->hostname . ':' . $this->port . '?' . http_build_query($data);
        }

        public function getHandler(): string
        {
            return 'redis';
        }
    }

}
