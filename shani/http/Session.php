<?php

/**
 * Description of Session
 *
 * @author coder
 */

namespace shani\http {

    use library\Cookie;
    use library\Map;
    use library\schema\DBase;

    final class Session
    {

        private static DBase $conn;
        private string $userId, $cart;

        public function __construct(App &$app, string $name)
        {
            $this->cart = $name;
            $app->on('web', function ()use (&$app) {
                $this->userId = $app->request->cookies($app->config->sessionName());
            })->on('api', function () {

            });
        }

        /**
         * Returns the number of key-value mappings in this map.
         * @return int
         */
        public function size(): int
        {
//            $query = 'SELECT COUNT(*) AS total FROM Users u, UserCarts uc, CartData cd WHERE u.userId = :uid ';
//            $query .= 'AND uc.userId_fk = u.userId AND uc.cartId = cd.cartId_fk';
            $row = self::$conn->getResult('SELECT getCartSize(:uid) AS size', ['uid' => $this->userId]);
            return $row[0]['total'] ?? 0;
        }

        /**
         * Associates the specified value with the specified key in this map.
         * If the key already exists, it will be skipped.
         * @param string $key A key to put in a map
         * @param string $value A value to associate with the key.
         * @return self
         */
        public function add(string $key, string $value): self
        {
            self::$conn->getResult('SELECT add2Cart(:uid,:cart,:key,:val) AS result', [
                'uid' => $this->userId, 'cart' => $this->cart, 'key' => $key, 'val' => $value
            ]);
            return $this;
        }

        /**
         * Copies all of the mappings from the specified map to this map. If the
         * key already exists, it will be skipped.
         * @param array $items items to copy from
         * @param string|array|null $keys If provided, only items with specified
         * keys will be added
         * @param bool $selected If true, the function will add only the selected
         * items matches with $keys, and vice versa
         * @return self
         * @see self::add()
         */
        public function addAll(array $items, string|array|null $keys = null, bool $selected = true): self
        {

        }

        /**
         * Associates the specified value with the specified key in this map.
         * If the key already exists, it will be overwritten with  a new value.
         * @param string $key A key to put in a map
         * @param string $value A value to associate with the key.
         * @return self
         */
        public function put(string $key, string $value): self
        {
            self::$conn->getResult('SELECT put2Cart(:uid,:cart,:key,:val) AS result', [
                'uid' => $this->userId, 'cart' => $this->cart, 'key' => $key, 'val' => $value
            ]);
            return $this;
        }

        /**
         * Copies all of the mappings from the specified map to this map. If the
         * key already exists, it will be overwritten.
         * @param array $items items to copy from
         * @param string|array|null $keys If provided, only items with specified
         * keys will be added
         * @param bool $selected If true, the function will add only the selected
         * items matches with $keys, and vice versa
         * @return self
         * @see self::add()
         */
        public function putAll(array $items, string|array|null $keys = null, bool $selected = true): self
        {

        }

        /**
         * Returns the value to which the specified key is mapped, or null
         * if this map contains no mapping for the key.
         * @param string $key A key to get value from
         * @return string|null
         */
        public function get(string $key): ?string
        {
            $row = self::$conn->getResult('SELECT getFromCart(:uid,:cart,:key) AS result', [
                'uid' => $this->userId, 'cart' => $this->cart, 'key' => $key
            ]);
            return $row[0]['result'];
        }

        /**
         * Returns true if this map contains a mapping for the specified key.
         * @param string|array $keys Key(s) to check on
         * @return bool Returns true if all keys exists, false otherwise
         */
        public function has(string|array $keys): bool
        {

        }

        /**
         * Removes all of the mappings from this map.
         * @return bool Returns true on success, false otherwise
         */
        public function clear(): bool
        {
            $row = self::$conn->getResult('SELECT clearCart(:uid,:cart) AS result', [
                'uid' => $this->userId, 'cart' => $this->cart
            ]);
            return !empty($row[0]['result']);
        }

        /**
         * Apply a callback for each entry in this map until all entries
         * have been processed or the action throws an exception.
         * @param callable $cb A callback that accepts $key and $value as arguments.
         * A callback must return a pair of key-value as associative array
         * @return self
         */
        public function each(callable $cb): self
        {

        }

        /**
         * Removes the mapping for the specified key from this map if present.
         * @param string|array $keys key(s) to remove
         * @return self
         */
        public function remove(string|array $keys): self
        {

        }

        /**
         * Returns an array of keys contained in this map.
         * @return array|null
         */
        public function keys(): ?array
        {

        }

        /**
         * Attempts to compute a mapping for the specified key and its current
         * mapped value (or null if there is no current mapping).
         * @param string $key A mapping key to compute from
         * @param callable $cb A callback function that accepts key and value as arguments
         * @return self
         */
        public function compute(string $key, callable $cb): self
        {

        }

        /**
         * Apply a callback function to every entry of a map and return a copy of new map
         * @param callable $cb A callback function that accepts key and value as arguments.
         * A callback must return true
         * @return array|null
         */
        public function where(callable $cb): ?array
        {

        }

        /**
         * Returns an array of values contained in this map.
         * @return array|null
         */
        public function values(): ?array
        {

        }

        /**
         * Returns an associative array of the mappings contained in this map.
         * @return array|null
         */
        public function set(): ?array
        {

        }

        /**
         * Returns true if this map contains no key-value mappings.
         * @return bool
         */
        public function isEmpty(): bool
        {

        }

        /**
         * Reduce an array to a scalar value, for example when wanting to find sum or average of array
         * @param array $rows A multidimensional array to reduce
         * @param callable $cb a callback that accepts two arguments, a single array
         * and accumulator where type of accumulator is same as that of <code>$initialValue</code>
         * @param string|float $initialValue An initial accumulator value
         * @return type A single scalar value
         * @see Map::reduce()
         */
        public function reduce(callable $cb, string|float $initialValue)
        {
            return Map::reduce($this->data[$this->name], $cb, $initialValue);
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

        public static function start(App &$app): void
        {
            $app->on('web', function (App &$app) {
                if (!isset(self::$conn)) {
                    self::initDB();
                }
//                if (!$app->request->isAsync()) {
//                    session_regenerate_id();
//                }
                $sessName = $app->config->sessionName();
                $cookie = (new Cookie())->setName($sessName)
                        ->setValue($app->request->cookies($sessName) ?? session_create_id())
                        ->setSecure($app->request->uri->secure())->setHttpOnly(true)
                        ->setDomain($app->request->uri->hostname)
                        ->setMaxAge($app->config->cookieMaxAge());
                $app->response->setCookie($cookie);
            });
        }

        private static function initDB()
        {
            self::$conn = new DBase('sqlite', 'db');
//            self::$conn->getResult($creational);
        }
    }

}