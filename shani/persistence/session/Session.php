<?php

/**
 * Description of SessionStorage
 * @author coder
 *
 * Created on: Mar 20, 2025 at 12:59:35 PM
 */

namespace shani\persistence\session {


    final class Session implements \JsonSerializable
    {

        public readonly int $createdAt;
        private int $lastActive;
        private array $carts = [];

        public function __construct(int $createdAt, int $lastActive)
        {
            $this->createdAt = $createdAt;
            $this->lastActive = $lastActive;
        }

        private function add(Cart $cart): self
        {
            $this->carts[$cart->name] = $cart;
            return $this;
        }

        public function delete(string $cartName): self
        {
            unset($this->carts[$cartName]);
            return $this;
        }

        public function getLastActive(): int
        {
            return $this->lastActive;
        }

        public function clear(): self
        {
            $this->carts = [];
            return $this;
        }

        public function has(string $cartName): bool
        {
            return array_key_exists($cartName, $this->carts);
        }

        public function hasAll(array $cartNames): bool
        {
            foreach ($cartNames as $name) {
                if (!array_key_exists($name, $this->carts)) {
                    return false;
                }
            }
            return true;
        }

        public function touch(): self
        {
            $this->lastActive = time();
            return $this;
        }

        public function count(): int
        {
            return count($this->carts);
        }

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

        public static function fromJson(string $json): self
        {
            $data = json_decode($json, true);
            $session = new Session($data['createdAt'], $data['lastActive']);
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
