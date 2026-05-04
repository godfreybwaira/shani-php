<?php

/**
 * Description of StatelessSessionStorage
 * @author goddy
 *
 * Created on: Apr 5, 2026 at 6:58:47 PM
 */

namespace features\session {

    use features\ds\map\WritableMap;

    final class StatelessSessionStorage implements SessionStorageInterface
    {

        private array $carts = [];

        public final function cartExists(string $cartName): bool
        {
            return isset($this->carts[$cartName]);
        }

        public function cart(string $cartName): WritableMap
        {
            if (!isset($this->carts[$cartName])) {
                $this->carts[$cartName] = new WritableMap();
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
