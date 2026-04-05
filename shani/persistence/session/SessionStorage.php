<?php

/**
 * Description of SessionStorage
 * @author goddy
 *
 * Created on: Apr 3, 2026 at 7:27:49 PM
 */

namespace shani\persistence\session {

    use lib\ds\map\MutableMap;
    use shani\http\App;

    abstract class SessionStorage implements SessionStorageInterface
    {

        protected array $carts = [];

        public final function cartExists(string $cartName): bool
        {
            return isset($this->carts[$cartName]);
        }

        protected final function createCart(string $cartName, array $data): MutableMap
        {
            if (!isset($this->carts[$cartName])) {
                $this->carts[$cartName] = new MutableMap($data);
            }
            return $this->carts[$cartName];
        }

        public static function getStorage(App $app, SessionStorageChooser $choice): SessionStorageInterface
        {
            return match ($choice) {
                SessionStorageChooser::FILE => new FileSessionStorage($app),
                default => new MemorySessionStorage()
            };
        }
    }

}