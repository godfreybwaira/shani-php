<?php

namespace features\persistence\session {

    use features\ds\map\MutableMap;

    /**
     * SessionStorageInterface
     *
     * Defines the contract for managing PHP sessions in a secure, flexible,
     * and maintainable way.
     *
     * This interface provides:
     * - Controlled session lifecycle (start, close, destroy)
     * - Safe session ID regeneration to mitigate session fixation attacks
     * - Multiple named carts stored as MutableMap objects (data persisted via toArray())
     *
     * Key Design Principles:
     * - Supports a "disabled" mode for testing, CLI, or async environments
     * - Carts are kept as objects during the request and serialized to plain arrays in $_SESSION
     * - Regeneration should be called explicitly on authentication changes (login/logout)
     *   rather than on every request to avoid race conditions
     *
     * @package features\persistence\session
     *
     * @author goddy
     *
     * Created on: Apr 4, 2026 at 5:57:12 PM
     */
    interface SessionStorageInterface
    {

        /**
         * Completely destroys the current session.
         *
         * This method:
         * - Removes all session data from $_SESSION
         * - Destroys the session on the server
         * - Clears the session cookie from the client
         * - Clears any in-memory carts managed by the SessionManager
         *
         * Use this on logout or when you want to end the user's session entirely.
         *
         * @return void
         */
        public function destroy(): void;

        /**
         * Persists all modified carts back to the $_SESSION superglobal.
         *
         * This method is automatically registered via `register_shutdown_function()`
         * in the SessionManager implementation, so you usually don't need to call it manually.
         *
         * Calling this multiple times is safe (idempotent due to internal flag).
         *
         * @return void
         */
        public function close(): void;

        /**
         * Checks whether the session has been started.
         *
         * In disabled mode, this always returns true.
         *
         * @return bool True if the session is active (or manager is disabled), false otherwise.
         */
        public function started(): bool;

        /**
         * Regenerates the session ID to enhance security.
         *
         * This is the recommended method to call after important security events:
         * - Successful user login
         * - Logout
         *
         * Important: Do **not** call this on every request to avoid race conditions.
         *
         * @return SessionStorageInterface
         */
        public function refresh(): SessionStorageInterface;

        /**
         * Returns (and lazily initializes) a named cart as a MutableMap.
         *
         * If the cart does not exist in the current request or session, a new empty
         * MutableMap (wrapped as Cart) is created.
         *
         * Multiple carts can coexist (e.g., 'cart', 'wishlist', 'comparison').
         *
         * The returned object is mutable during the request. Changes are persisted
         * automatically when `close()` is called at the end of the script.
         *
         * @param string $cartName Unique name of the cart.
         *
         * @return MutableMap The cart instance
         */
        public function cart(string $cartName): MutableMap;

        /**
         * Checks whether a cart with the given name exists in the current session.
         *
         * This method does **not** initialize a new cart if it doesn't exist.
         * Useful for conditional logic (e.g., show "Your cart is empty" vs. display items).
         *
         * @param string $cartName The name of the cart to check
         *
         * @return bool True if the cart exists and has been loaded or is present in $_SESSION
         */
        public function cartExists(string $cartName): bool;
    }

}
