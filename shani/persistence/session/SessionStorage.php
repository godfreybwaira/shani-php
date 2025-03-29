<?php

/**
 * Description of SessionStorage
 * @author coder
 *
 * Created on: Mar 20, 2025 at 12:59:35 PM
 */

namespace shani\persistence\session {

    use lib\IterableData;

    final class SessionStorage extends IterableData
    {

        private int $lastActive;
        private readonly int $createdAt;

        public function __construct(int $createdAt, int $lastActive)
        {
            $this->createdAt = $createdAt;
            $this->lastActive = $lastActive;
            parent::__construct([]);
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
         * Session cart is used for storing and retrieving session data.
         * @param string $name Cart name, if cart does not exists, it is created
         * otherwise the available cart object is returned.
         * @return Cart
         */
        public function cart(string $name): Cart
        {
            $this->touch();
            return ($this->data[$name] ??= new Cart($name));
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'createdAt' => $this->createdAt,
                'lastActive' => $this->lastActive,
                'carts' => $this->data
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
                $session->add($cart->name, $cart);
            }

            return $session;
        }
    }

}
