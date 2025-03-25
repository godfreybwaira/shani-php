<?php

/**
 * Description of SessionStorage
 * @author coder
 *
 * Created on: Mar 20, 2025 at 12:59:35 PM
 */

namespace shani\persistence\session {


    final class Session implements \JsonSerializable, \Stringable
    {

        private int $lastActive;
        private array $carts = [];
        private readonly int $createdAt;

        public function __construct(int $createdAt, int $lastActive)
        {
            $this->createdAt = $createdAt;
            $this->lastActive = $lastActive;
        }

        /**
         * Add item to a session object
         * @param Cart $cart
         * @return self
         */
        private function add(Cart $cart): self
        {
            $this->carts[$cart->name] = $cart;
            return $this;
        }

        /**
         * Delete carts mentioned
         * @param array $cartName List of carts to delete
         * @return self
         */
        public function delete(string ...$cartName): self
        {
            foreach ($cartName as $name) {
                unset($this->carts[$name]);
            }
            return $this;
        }

        /**
         * Get the last access time on a session object
         * @return int
         */
        public function getLastActive(): int
        {
            return $this->lastActive;
        }

        /**
         * Check if all carts mentioned exist in the current session object
         * @param string $cartName List of cart names
         * @return bool Returns true if all carts exists, false otherwise.
         */
        public function has(string ...$cartName): bool
        {
            foreach ($cartName as $name) {
                if (!array_key_exists($name, $this->carts)) {
                    return false;
                }
            }
            return true;
        }

        /**
         * Remove all carts in a session object
         * @return self
         */
        public function clear(): self
        {
            $this->carts = [];
            return $this;
        }

        /**
         * Update the last active session time to a current time. This function
         * is called every time <code>cart</code> function is called.
         * @return self
         */
        public function touch(): self
        {
            $this->lastActive = time();
            return $this;
        }

        /**
         * Returns total number of carts available
         * @return int
         */
        public function count(): int
        {
            return count($this->carts);
        }

        /**
         * Session cart is used for storing and retrieving session data.
         * @param string $name Cart name, if cart does not exists, it is created
         * otherwise the available cart object is returned.
         * @return Cart
         */
        public function cart(string $name): Cart
        {
            $this->touch();
            return ($this->carts[$name] ??= new Cart($name));
        }

        #[\Override]
        public function __toString()
        {
            return json_encode($this);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'createdAt' => $this->createdAt,
                'lastActive' => $this->lastActive,
                'carts' => $this->carts
            ];
        }

        /**
         * Create session cart from JSON data
         * @param string $json
         * @return self
         */
        public static function fromJson(string $json): self
        {
            $data = json_decode($json, true);
            $session = new self($data['createdAt'], $data['lastActive']);
            foreach ($data['carts'] as $name => $values) {
                $cart = new Cart($name);
                foreach ($values as $key => $value) {
                    $cart->add($key, $value);
                }
                $session->add($cart);
            }

            return $session;
        }
    }

}
