<?php

/**
 * Description of Session
 *
 * @author coder
 */

namespace shani\engine\http {

    use library\Map;

    final class Session
    {

        private array $data;
        private string $name;

        public function __construct(App &$app, string $name)
        {
            $this->name = $name;
            $app->web(function () use (&$name) {
                $this->data[$name] = $_SESSION[$name] ?? [];
                unset($_SESSION[$name]);
            })->api(function () use (&$name) {
                $this->data[$name] = [];
            });
        }

        public function __toString(): string
        {
            return serialize($this->data[$this->name]);
        }

        /**
         * Add items to session storage
         * @param array $items Items to add to session storage
         * @param string|array|null $keys If provided, only items with specified
         * keys will be considered
         * @param bool $selected If true, the function will add only the selected
         * items matches with $keys, and vice versa
         * @return self
         */
        public function add(array $items, string|array|null $keys = null, bool $selected = true): self
        {
            $this->data[$this->name] = Map::add($items, $this->data[$this->name], $keys, $selected);
            return $this;
        }

        /**
         * Clears the array then add new items
         * @param array $items Items to add to session storage
         * @param string|array $keys If provided, only items with specified keys will be considered
         * @param bool $selected If true, the function will add only the selected
         * items matches with $keys, and vice versa
         * @return self
         */
        public function put(array $items, string|array $keys = null, bool $selected = true): self
        {
            $this->data[$this->name] = Map::add($items, $keys, $selected);
            return $this;
        }

        /**
         * Remove session object and all it's content
         * @return self
         */
        public function clear(): self
        {
            $this->data[$this->name] = [];
            return $this;
        }

        /**
         * Check if session object has an item or items
         * @param string|array $keys
         * @return bool Returns true on success, false otherwise
         */
        public function has($keys): bool
        {
            return Map::has($this->data[$this->name], $keys);
        }

        /**
         * Iterate over an array using user supplied callback function. This function
         * will change the original array.
         * @param callable $cb User supplied callback with the following signature:
         * $callback($array):array
         * @return array The entire array
         */
        public function each(callable $cb): array
        {
            return Map::each($this->data[$this->name], $cb);
        }

        public function reduce(callable $cb)
        {
            return Map::reduce($this->data[$this->name], $cb);
        }

        public function min(string $key): float
        {
            return min($this->data[$this->name], $key);
        }

        public function max(string $key): float
        {
            return max($this->data[$this->name], $key);
        }

        public function average(string $key): float
        {
            return Map::average($this->data[$this->name], $key);
        }

        public function remove($keys, bool $selected = true): self
        {
            $this->data[$this->name] = Map::get($this->data[$this->name], $keys, !$selected);
            return $this;
        }

        public function find(callable $cb, int $limit = 0): array
        {
            return Map::find($this->data[$this->name], $cb, $limit);
        }

        public function compact(array $headers): array
        {
            return \library\DataConvertor::array2table($this->data[$this->name], $headers);
        }

        public function get($keys = null, bool $selected = true)
        {
            return Map::get($this->data[$this->name], $keys, $selected);
        }

        public static function stop(): void
        {
            session_unset();
            session_destroy();
        }

        public static function start(App &$app): void
        {
            $app->web(function (App &$app) {
                $sessId = $app->request()->cookies($app->config()->sessionName()) ?? session_create_id();
                $storageFile = session_save_path() . '/sess_' . $sessId;
                if (!is_file($storageFile)) {
                    touch($storageFile);
                } elseif (!$app->request()->isAsync()) {
//                    session_regenerate_id();
                }
                self::prepareCookie($app, $sessId);
            });
        }

        private static function prepareCookie(App &$app, string $value): void
        {
            $cookie = (new \library\HttpCookie())->setValue($value)
                    ->setSecure($app->request()->uri()->secure())->setHttpOnly(true)
                    ->setDomain($app->request()->uri()->hostname())
                    ->setMaxAge($app->config()->cookieMaxAge())
                    ->setName($app->config()->sessionName());
            $app->response()->setCookie($cookie);
        }

        private static function getData(string $sessionId): array
        {
            $content = file_get_contents(session_save_path() . '/sess_' . $sessionId);
            return !empty($content) ? unserialize($content) : [];
        }

        private static function saveData(string $sessionId, array &$data): bool
        {
            $values = serialize(array_merge(self::getData($sessionId), $data));
            return !empty(file_put_contents(session_save_path() . '/sess_' . $sessionId, $values));
        }
    }

}