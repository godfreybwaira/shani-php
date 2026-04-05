<?php

/**
 * Description of MemorySessionStorage
 * @author goddy
 *
 * Created on: Apr 5, 2026 at 6:58:47 PM
 */

namespace shani\persistence\session {

    use lib\ds\map\MutableMap;

    final class MemorySessionStorage extends SessionStorage
    {

        public function cart(string $cartName): MutableMap
        {
            return $this->createCart($cartName, []);
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
