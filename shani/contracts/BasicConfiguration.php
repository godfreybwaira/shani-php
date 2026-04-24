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
    use shani\config\AppConfig;
    use shani\config\AuthenticationConfig;
    use shani\config\CsrfConfig;
    use shani\config\PathConfig;
    use shani\config\RequestConfig;
    use shani\config\SessionConfig;
    use shani\config\WebPolicyConfig;

    /**
     * Abstract base class for defining application configurations.
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
     * - pathConfig(): PathConfig
     * - isAsync(): bool
     * - runTest(): TestResult
     *
     * By default, most methods return safe defaults (e.g., disabled authentication,
     * local storage, null database connection) unless overridden by subclasses.
     */
    abstract class BasicConfiguration
    {

        /**
         * Application instance reference.
         *
         * @var App
         */
        protected readonly App $app;

        /**
         * Authentication related configurations.
         *
         * @var AuthenticationConfig|null
         */
        protected ?AuthenticationConfig $authenticationConfig = null;

        /**
         * Session related configurations.
         *
         * @var SessionConfig|null
         */
        protected ?SessionConfig $sessionConfig = null;

        /**
         * CSRF related configurations.
         *
         * @var CsrfConfig|null
         */
        protected ?CsrfConfig $csrfConfig = null;

        /**
         * Request related configurations.
         *
         * @var RequestConfig|null
         */
        protected ?RequestConfig $requestConfig = null;

        /**
         * Application related configurations.
         *
         * @var AppConfig|null
         */
        protected ?AppConfig $appConfig = null;

        /**
         * Web policy related configurations.
         *
         * @var WebPolicyConfig|null
         */
        protected ?WebPolicyConfig $webPolicyConfig = null;

        /**
         * Storage media interface implementation.
         *
         * @var StorageMediaInterface|null
         */
        protected ?StorageMediaInterface $storageMedia = null;

        /**
         * Constructor for BasicConfiguration.
         *
         * Initializes the base configurations with the application instance.
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
         * @return AuthenticationConfig
         */
        public function authenticationConfig(): AuthenticationConfig
        {
            return $this->authenticationConfig ??= new AuthenticationConfig();
        }

        /**
         * Session related configurations.
         *
         * @return SessionConfig
         */
        public function sessionConfig(): SessionConfig
        {
            return $this->sessionConfig ??= new SessionConfig();
        }

        /**
         * CSRF related configurations.
         *
         * @return CsrfConfig
         */
        public function csrfConfig(): CsrfConfig
        {
            return $this->csrfConfig ??= new CsrfConfig($this->app->request->method);
        }

        /**
         * Request related configurations.
         *
         * @return RequestConfig
         */
        public function requestConfig(): RequestConfig
        {
            return $this->requestConfig ??= new RequestConfig($this->app->request->method);
        }

        /**
         * Web related configurations.
         *
         * @return WebPolicyConfig
         */
        public function webPolicyConfig(): WebPolicyConfig
        {
            return $this->webPolicyConfig ??= new WebPolicyConfig();
        }

        /**
         * Application related configurations.
         *
         * @return AppConfig
         */
        public function appConfig(): AppConfig
        {
            return $this->appConfig ??= new AppConfig();
        }

        /**
         * Application path related configurations.
         *
         * @return PathConfig
         */
        public abstract function pathConfig(): PathConfig;

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
