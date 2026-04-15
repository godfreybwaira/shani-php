<?php

/**
 * Description of MemorySessionStorage
 * @author goddy
 *
 * Created on: Apr 5, 2026 at 6:58:47 PM
 */

namespace features\persistence\session {

    use lib\ds\map\MutableMap;

    final class MemorySessionStorage implements SessionStorageInterface
    {

        protected array $carts = [];

        public final function cartExists(string $cartName): bool
        {
            return isset($this->carts[$cartName]);
        }

        public function cart(string $cartName): MutableMap
        {
            if (!isset($this->carts[$cartName])) {
                $this->carts[$cartName] = new MutableMap();
            }
            return $this->carts[$cartName];
        }

        public function close(): void
        {
            return;
        }

        public function destroy(): void
        {
            return;
        }

        public function refresh(): SessionStorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            return true;
        }
    }

}
