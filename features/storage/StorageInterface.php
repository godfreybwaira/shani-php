<?php

namespace features\storage {

    use features\ds\map\WriteMap;

    /**
     * StorageInterface
     *
     * Defines the contract for managing stateful storage in a secure,
     * flexible, and maintainable way. This can represent PHP sessions,
     * cache layers, or other persistence mechanisms.
     *
     * This interface provides:
     * - Controlled lifecycle (start, close, destroy)
     * - Safe refresh/regeneration to mitigate stale or insecure identifiers
     * - Multiple named containers stored as WriteMap objects (data persisted via toArray())
     *
     * Key Design Principles:
     * - Supports a "disabled" mode for testing, CLI, or async environments
     * - Containers are kept as objects during the request and serialized to plain arrays
     * - Refresh should be called explicitly on authentication or cache invalidation events
     *
     * @package features\storage
     *
     * @author goddy
     *
     * Created on: Apr 4, 2026 at 5:57:12 PM
     */
    interface StorageInterface
    {

        /**
         * Completely destroys the current storage context.
         *
         * This method:
         * - Removes all data from the underlying storage
         * - Destroys the storage instance on the server
         * - Clears any client identifiers (e.g., cookies for sessions)
         * - Clears any in-memory containers managed by the StorageManager
         *
         * Use this on logout or when you want to invalidate the cache/session entirely.
         *
         * @return void
         */
        public function destroy(): void;

        /**
         * Remove all items from the current storage context. The storage context itself remains
         * @return self
         */
        public function clear(): self;

        /**
         * Persists all modified containers back to the underlying storage.
         *
         * This method is typically registered via `register_shutdown_function()`
         * in the StorageManager implementation, so you usually don't need to call it manually.
         *
         * Calling this multiple times is safe (idempotent due to internal flag).
         *
         * @return void
         */
        public function close(): void;

        /**
         * Checks whether the storage has been started/initialized.
         *
         * In disabled mode, this always returns true.
         *
         * @return bool True if the storage is active (or manager is disabled), false otherwise.
         */
        public function started(): bool;

        /**
         * Refreshes the storage identifier to enhance security or invalidate stale data.
         *
         * Recommended after important events:
         * - Successful user login/logout (sessions)
         * - Cache invalidation (caches)
         *
         * @return StorageInterface
         */
        public function refresh(): StorageInterface;

        /**
         * Returns (and lazily initializes) a named container as a WriteMap.
         *
         * If the container does not exist in the current request or storage,
         * a new empty WriteMap is created.
         *
         * Multiple containers can coexist (e.g., 'cart', 'wishlist', 'cache_bucket').
         *
         * @param string $name Unique name of the container.
         *
         * @return WriteMap The container instance
         */
        public function container(string $name): WriteMap;

        /**
         * Checks whether a container with the given name exists in the current storage.
         *
         * This method does **not** initialize a new container if it doesn't exist.
         * Useful for conditional logic (e.g., show "Your cart is empty" vs. display items,
         * or check if a cache bucket exists).
         *
         * @param string $name The name of the container to check
         *
         * @return bool True if the container exists and has been loaded or is present in storage
         */
        public function containerExists(string $name): bool;
    }

}
