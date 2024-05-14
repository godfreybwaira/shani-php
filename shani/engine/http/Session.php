<?php

/**
 * Description of Session
 *
 * @author coder
 */

namespace shani\engine\http {

    use library\Map;

    final class Session implements \shani\adaptor\Handler
    {

        private string $name, $id;
        private ?array $rows = null;
        private static \shani\adaptor\Cacheable $memory;

        public function __construct(string $id, string $name)
        {
            $this->id = $id;
            $this->name = $name;
        }

        private function data(): ?array
        {
            if (empty($this->rows)) {
                $this->rows = self::$memory->get($this->id, $this->name);
            }
            return $this->rows;
        }

        public function add(array $items, $keys = null, bool $selected = true): self
        {
            $data = Map::get($items, $keys, $selected);
            self::$memory->add($this->id, [$this->name => $data]);
            return $this;
        }

        public function put(array $items, $keys = null, bool $selected = true): self
        {
            $data = Map::get($items, $keys, $selected);
            self::$memory->replace($this->id, [$this->name => $data]);
            return $this;
        }

        public function clear(): self
        {
            self::$memory->remove($this->id, $this->name);
            return $this;
        }

        public static function stop(App &$app): void
        {
            $id = $app->request()->cookies($app->config()->sessionName());
            self::$memory->delete($id);
        }

        public static function start(App &$app): ?string
        {
            $sessId = $app->request()->cookies($app->config()->sessionName());
            if (!$app->request()->isAjax() || $sessId === null) {
                $newId = base64_encode(random_bytes(rand(14, 35)));
                if ($sessId !== null) {
                    self::$memory->rename($sessId, $newId);
                }
                $cookie = (new \library\HttpCookie())->setValue($newId)
                        ->setSecure($app->request()->secure())->setHttpOnly(true)
                        ->setDomain($app->request()->uri()->hostname())
                        ->setMaxAge($app->config()->cookieMaxAge())
                        ->setName($app->config()->sessionName());
                $app->response()->setCookie($cookie);
                return $newId;
            }
            return $sessId;
        }

        public function has($keys): bool
        {
            return Map::has($this->data(), $keys);
        }

        public function exists(): bool
        {
            return self::$memory->exists($this->id, $this->name);
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
            self::$memory->replace($this->id, [
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
            return \library\DataConvertor::php2compact($this->data(), $headers);
        }

        public function get($keys = null, bool $selected = true)
        {
            return Map::get($this->data(), $keys, $selected);
        }

        public static function setHandler($handler): void
        {
            self::$memory = $handler;
        }
    }

}