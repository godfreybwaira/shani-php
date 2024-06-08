<?php

/**
 * Description of Session
 *
 * @author coder
 */

namespace shani\engine\http {

    final class Session implements \shani\adaptor\Handler
    {

        private static \shani\adaptor\Cacheable $memory;
        private static string $storageId;
        private string $name;

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        private function data(): ?array
        {
            if (empty($this->rows)) {
                $this->rows = self::$memory->get(self::$storageId, $this->name);
            }
            return $this->rows;
        }

        public function add(array $items, $keys = null, bool $selected = true): self
        {
            $data = Map::get($items, $keys, $selected);
            self::$memory->add(self::$storageId, [$this->name => $data]);
            return $this;
        }

        public function put(array $items, $keys = null, bool $selected = true): self
        {
            $data = Map::get($items, $keys, $selected);
            self::$memory->replace(self::$storageId, [$this->name => $data]);
            return $this;
        }

        /**
         * Remove all items in session object
         * @return self
         */
        public function clear(): self
        {
            self::$memory->remove(self::$storageId, $this->name);
            return $this;
        }

        /**
         * Change session id to new id
         * @param string $newId New session Id
         * @return self
         */
        public function rename(string $newId): self
        {
            self::$memory->rename(self::$storageId, $newId);
        }

        /**
         * Check if session object has an item or items
         * @param string|array $keys
         * @return bool Returns true on success, false otherwise
         */
        public function has($keys): bool
        {
            return Map::has($this->data(), $keys);
        }

        public function exists(): bool
        {
            return self::$memory->exists(self::$storageId, $this->name);
        }

        public function filled(?array $keys = null): bool
        {
            return Map::filled($this->data(), $keys);
        }

        public function each(callable $cb): array
        {
            return Map::each($this->data(), $cb);
        }

        public function reduce(callable $cb)
        {
            return Map::reduce($this->data(), $cb);
        }

        public function min(string $key): float
        {
            return min($this->data(), $key);
        }

        public function max(string $key): float
        {
            return max($this->data(), $key);
        }

        public function average(string $key): float
        {
            return Map::average($this->data(), $key);
        }

        public function remove($keys, bool $selected = true): self
        {
            self::$memory->replace(self::$storageId, [
                $this->name => Map::get($this->data(), $keys, !$selected)
            ]);
            return $this;
        }

        public function find(callable $cb, int $limit = 0): array
        {
            return Map::find($this->data(), $cb, $limit);
        }

        public function compact(array $headers): array
        {
            return \library\DataConvertor::array2compact($this->data(), $headers);
        }

        public function get($keys = null, bool $selected = true)
        {
            return Map::get($this->data(), $keys, $selected);
        }

        public static function stop(): void
        {
            self::$memory->delete(self::$storageId);
        }

        public static function start(App &$app): void
        {
            $sessId = $app->request()->cookies($app->config()->sessionName());
            if (!$app->request()->isAsync() || $sessId === null) {
                $newId = base64_encode(random_bytes(rand(14, 40)));
                if ($sessId !== null) {
                    self::$memory->rename($sessId, $newId);
                }
                $cookie = (new \library\HttpCookie())->setValue($newId)
                        ->setSecure($app->request()->secure())->setHttpOnly(true)
                        ->setDomain($app->request()->uri()->hostname())
                        ->setMaxAge($app->config()->cookieMaxAge())
                        ->setName($app->config()->sessionName());
                $app->response()->setCookie($cookie);
                self::$storageId = $newId;
            } else {
                self::$storageId = $sessId;
            }
        }

        public static function setHandler($handler): void
        {
            self::$memory = $handler;
        }
    }

}