<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\contracts {

    use features\crypto\Encryption;
    use features\logging\LoggingLevel;
    use features\middleware\MiddlewareHandlerInterface;
    use features\oauth2\Oauth2Repository;
    use features\persistence\DatabaseInterface;
    use features\storage\LocalStorage;
    use features\storage\StorageMediaInterface;
    use features\test\TestResult;
    use shani\assets\StaticAssetServers;
    use shani\http\RequestRoute;
    use shani\launcher\App;
    use shani\presets\AppPresets;
    use shani\presets\AuthenticationPresets;
    use shani\presets\CsrfPresets;
    use shani\presets\PathPresets;
    use shani\presets\RequestPresets;
    use shani\presets\SessionPresets;
    use shani\presets\WebPolicyPresets;

    /**
     * Abstract base class for defining application presets and configurations.
     *
     * This class provides a centralized way to define and access common application
     * configurations such as authentication, session handling, CSRF protection,
     * request policies, web policies, application settings, and paths.
     *
     * It also defines hooks for:
     * - Error handling
     * - Middleware registration
     * - Resource access checks (public/guest)
     * - Storage media selection
     * - Database and OAuth2 repository access
     * - Static asset server selection
     * - Running unit/integration tests
     *
     * Subclasses must implement:
     * - pathPresets(): PathPresets
     * - isAsync(): bool
     * - runTest(): TestResult
     *
     * By default, most methods return safe defaults (e.g., disabled authentication,
     * local storage, null database connection) unless overridden by subclasses.
     */
    abstract class BasicPresets
    {

        /**
         * Application instance reference.
         *
         * @var App
         */
        protected readonly App $app;

        /**
         * Authentication related presets.
         *
         * @var AuthenticationPresets|null
         */
        protected ?AuthenticationPresets $authenticationPresets = null;

        /**
         * Session related presets.
         *
         * @var SessionPresets|null
         */
        protected ?SessionPresets $sessionPresets = null;

        /**
         * CSRF related presets.
         *
         * @var CsrfPresets|null
         */
        protected ?CsrfPresets $csrfPresets = null;

        /**
         * Request related presets.
         *
         * @var RequestPresets|null
         */
        protected ?RequestPresets $requestPresets = null;

        /**
         * Application related presets.
         *
         * @var AppPresets|null
         */
        protected ?AppPresets $appPresets = null;

        /**
         * Web policy related presets.
         *
         * @var WebPolicyPresets|null
         */
        protected ?WebPolicyPresets $webPolicyPresets = null;

        /**
         * Storage media interface implementation.
         *
         * @var StorageMediaInterface|null
         */
        protected ?StorageMediaInterface $storageMedia = null

        /**
         * Constructor for BasicPresets.
         *
         * Initializes the base presets with the application instance.
         *
         * @param App $app
         *     The application object providing access to request, logger, and other components.
         */
        protected function __construct(App $app)
        {
            $this->app = $app;
        }

        /**
         * Authentication related configurations.
         *
         * @return AuthenticationPresets
         */
        public function authenticationPresets(): AuthenticationPresets
        {
            return $this->authenticationPresets ??= new AuthenticationPresets();
        }

        /**
         * Session related configurations.
         *
         * @return SessionPresets
         */
        public function sessionPresets(): SessionPresets
        {
            return $this->sessionPresets ??= new SessionPresets();
        }

        /**
         * CSRF related configurations.
         *
         * @return CsrfPresets
         */
        public function csrfPresets(): CsrfPresets
        {
            return $this->csrfPresets ??= new CsrfPresets($this->app->request->method);
        }

        /**
         * Request related configurations.
         *
         * @return RequestPresets
         */
        public function requestPresets(): RequestPresets
        {
            return $this->requestPresets ??= new RequestPresets($this->app->request->method);
        }

        /**
         * Web related configurations.
         *
         * @return WebPolicyPresets
         */
        public function webPolicyPresets(): WebPolicyPresets
        {
            return $this->webPolicyPresets ??= new WebPolicyPresets();
        }

        /**
         * Application related configurations.
         *
         * @return AppPresets
         */
        public function appPresets(): AppPresets
        {
            return $this->appPresets ??= new AppPresets();
        }

        /**
         * Application path related configurations.
         *
         * @return PathPresets
         */
        public abstract function pathPresets(): PathPresets;

        /**
         * Check if a requesting domain (origin) is allowed to access resources.
         *
         * @param string $domain Domain name (FQDN) or IP address
         * @return bool True if whitelisted, false otherwise
         */
        public function whitelistedDomain(string $domain): bool
        {
            return false;
        }

        /**
         * Check if HTTP request is asynchronous (e.g., AJAX).
         *
         * @return bool True if asynchronous, false otherwise
         */
        public abstract function isAsync(): bool;

        /**
         * Handle application errors (default logs error).
         *
         * @param \Throwable $t Error object
         * @return RequestRoute|null A request route object or null
         */
        public function errorHandler(\Throwable $t): ?RequestRoute
        {
            $this->app->logger()->log(LoggingLevel::ERROR, $t->getMessage());
            return null;
        }

        /**
         * Register user-defined middlewares.
         *
         * @return MiddlewareHandlerInterface|null Middleware handler object, or null
         */
        public function getMiddlewareHandler(): ?MiddlewareHandlerInterface
        {
            return null;
        }

        /**
         * Check if a resource is available to both authenticated and unauthenticated users.
         *
         * @return bool True if public, false otherwise
         */
        public function accessingPublicResource(): bool
        {
            return false;
        }

        /**
         * Check if a resource is available only to unauthenticated users.
         *
         * @return bool True if guest-only, false otherwise
         */
        public function accessingGuestResource(): bool
        {
            return false;
        }

        /**
         * Get file storage media where uploaded files will be saved and retrieved.
         *
         * @return StorageMediaInterface
         */
        public function getStorageMedia(): StorageMediaInterface
        {
            return $this->storageMedia ??= new LocalStorage($this->app);
        }

        /**
         * Run unit and integration tests.
         *
         * @return TestResult
         */
        public static abstract function runTest(): TestResult;

        /**
         * Get database connection object.
         *
         * @return DatabaseInterface|null Database object or null
         */
        public function getDatabase(): ?DatabaseInterface
        {
            return null;
        }

        /**
         * Get OAuth 2.0 repository for data exchange.
         *
         * @return Oauth2Repository|null OAuth 2.0 repository object or null
         */
        public function getOauth2Repository(): ?Oauth2Repository
        {
            return null;
        }

        /**
         * Select preferred static asset server.
         *
         * @return StaticAssetServers
         */
        public function getStaticAssetServer(): StaticAssetServers
        {
            return StaticAssetServers::SHANI;
        }
    }

}
