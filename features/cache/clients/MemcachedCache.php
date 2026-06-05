<?php

namespace features\cache\clients {

    use features\ds\map\WriteMap;
    use features\storage\StorageInterface;

    /**
     * Memcached-based cache implementation.
     *
     * Stores and retrieves values from a Memcached server with
     * optional TTL support. Persists in-memory containers on shutdown.
     *
     * @author goddy
     *
     * Created on: May 21, 2026 at 12:51:14 PM
     */
    final class MemcachedCache implements StorageInterface
    {

        private bool $closed = false;

        /** @var array<string, WriteMap> */
        private array $carts = [];
        private readonly string $prefix;
        private readonly \Memcached $client;

        /**
         * Create a Memcached cache server instance.
         *
         * @param string $prefix
         * @param string $host The host name of the memcached server.            
         * @param int $port The port on which memcached is running. Usually, this is 11211
         */
        public function __construct(string $prefix, string $host, int $port = 11211)
        {
            $this->prefix = $prefix;
            $this->client = new \Memcached($prefix);
            $this->client->addServer($host, $port);
            register_shutdown_function([$this, 'close']);
        }

        public function container(string $name): WriteMap
        {
            if (!isset($this->carts[$name])) {
                $value = $this->getValue($name);
                $this->carts[$name] = new WriteMap($value ?? []);
            }
            return $this->carts[$name];
        }

        /**
         * Fetches a container value from Memcached.
         */
        private function getValue(string $name): mixed
        {
            $key = $this->makeKey($name);
            $value = $this->client->get($key);
            if ($this->client->getResultCode() === \Memcached::RES_SUCCESS) {
                return $value;
            }
            return null;
        }

        public function containerExists(string $name): bool
        {
            $key = $this->makeKey($name);
            $this->client->get($key);
            return $this->client->getResultCode() === \Memcached::RES_SUCCESS;
        }

        public function destroy(): void
        {
            $this->clear();
        }

        public function clear(): StorageInterface
        {
            $keys = [];
            foreach ($this->carts as $name => $v) {
                $keys[] = $this->makeKey($name);
            }
            $this->client->deleteMulti($keys);
            $this->carts = [];
            return $this;
        }

        public function refresh(): StorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            if (!extension_loaded('memcached')) {
                return false;
            }
            $versions = $this->client->getVersion();
            if (!is_array($versions) || empty($versions)) {
                return false;
            }
            foreach ($versions as $ver) {
                if ($ver === '0.0.0' || $ver === false) {
                    return false;
                }
            }
            return true;
        }

        public function close(): void
        {
            if (!$this->closed) {
                $items = [];
                foreach ($this->carts as $name => $cart) {
                    $items[$this->makeKey($name)] = $cart->toArray();
                }
                // store without TTL (0 = persistent until server eviction)
                $this->client->setMulti($items, 0);
                $this->closed = true;
            }
        }

        private function makeKey(string $name): string
        {
            return $name;
        }
    }

}
