<?php

/**
 * Description of StatelessSessionStorage
 * @author goddy
 *
 * Created on: Apr 5, 2026 at 6:58:47 PM
 */

namespace features\session {

    use features\ds\map\WritableMap;
    use features\storage\StorageInterface;

    final class StatelessSessionStorage implements StorageInterface
    {

        private array $carts = [];

        public final function containerExists(string $cartName): bool
        {
            return isset($this->carts[$cartName]);
        }

        public function container(string $cartName): WritableMap
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
            $this->clear();
            return;
        }

        public function clear(): StorageInterface
        {
            $this->carts = [];
            return $this;
        }

        public function refresh(): StorageInterface
        {
            return $this;
        }

        public function started(): bool
        {
            return true;
        }
    }

}
