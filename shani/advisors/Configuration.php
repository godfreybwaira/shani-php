<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\advisors {

    use lib\crypto\DigitalSignature;
    use lib\crypto\Encryption;
    use lib\DataCompression;
    use lib\oauth2\Oauth2Repository;
    use shani\advisors\web\BrowsingPrivacy;
    use shani\advisors\web\ContentSecurityPolicy;
    use shani\advisors\web\ResourceAccessPolicy;
    use shani\authentication\AuthenticationManager;
    use shani\contracts\StorageMedia;
    use shani\core\Framework;
    use shani\core\log\LogLevel;
    use shani\documentation\scanners\Endpoints;
    use shani\http\App;
    use shani\http\Middleware;
    use shani\http\RequestRoute;
    use shani\persistence\DatabaseConnection;
    use shani\persistence\LocalStorage;
    use shani\persistence\session\SessionConnectionInterface;
    use test\TestResult;

    abstract class Configuration
    {

        private ?string $permissionList, $userId;
        protected readonly App $app;

        protected function __construct(App $app)
        {
            $this->app = $app;
            $this->userId = null;
            $this->permissionList = null;
        }

        /**
         * Whether the current user is authenticated and has at least one permission
         * @var bool
         */
        public function isAuthenticated(): bool
        {
            if ($this->userId === null) {
                $strategies = $this->getAuthenticationStrategies();
                $manager = new AuthenticationManager($this->app, ...$strategies);
                $success = $manager->login();
                $this->permissionList = $manager->getPermissions();
                $this->userId = $manager->getUserId();
                return $success;
            }
            return false;
        }

        /**
         * Get authentication strategies objects that implements <code>AuthenticationStrategy</code>.
         * Developer must implement this interface to provide Authentication logic.
         * @return array Collection of AuthenticationStrategy objects
         *
         * @see self::skipAuthentication()
         */
        public function getAuthenticationStrategies(): array
        {
            return [];
        }

        /**
         * Get session connection handler. For more information see the implementation of <code>SessionConnectionInterface</code>
         * @return SessionConnectionInterface|null
         */
        public function getSessionConnection(): ?SessionConnectionInterface
        {
//            return null;
//            return new \shani\persistence\session\dto\MemcachedConnectionDto('localhost', 11211);
            return new \shani\persistence\session\dto\RedisConnectionDto('localhost', 6379);
        }

        /**
         * Get the path to application root directory.
         * @return string Application root directory
         */
        public abstract function root(): string;

        /**
         * Set or get Session cookie name.
         * @return string Cookie name
         */
        public function sessionName(): string
        {
            return 'sessionId';
        }

        /**
         * Get the directory inside application module where module controllers
         * resides.
         * <p>It is in this directory you will create GET, POST, PUT, DELETE
         * or any other custom HTTP method directories, These directories must be
         * in lowercase.</p>
         * @return string Path relative to current module directory
         */
        public function controllers(): string
        {
            return '/logic/controllers';
        }

        /**
         * Enable/disable CSRF protection mechanism.
         * @return bool True to skip, false otherwise.
         */
        public function skipCsrfProtection(): bool
        {
            return $this->app->platform() === 'api';
        }

        /**
         * Check if the current request method is protected from CSRF attacks
         * @return bool True if request method is protected, false otherwise.
         */
        public function csrfProtected(): bool
        {
            return str_contains('post,put,patch,delete', $this->app->request->method);
        }

        /**
         * Set default application language.
         * @return string Application language
         */
        public function defaultLanguage(): string
        {
            return 'sw';
        }

        /**
         * Check if HTTP request is requested via asynchronous mode for example
         * whether the request is made via AJAX or any other similar technology.
         * @return bool True if the request is asynchronous, false otherwise
         */
        public abstract function isAsync(): bool;

        /**
         * Application running state. Returns true if the application is running,
         * false otherwise.
         */
        public function isRunning(): bool
        {
            return true;
        }

        /**
         * Handle all application errors. You can use this function to log
         * application errors to your logger.
         * @param \Throwable $t Error Object
         * @return string|null A URI path as a fallback
         */
        public function errorHandler(\Throwable $t): ?string
        {
            $this->app->logger()->log(LogLevel::ERROR, $t->getMessage());
            return null;
        }

        /**
         * Get all application supported languages where a key is a language code
         * and a value is a language name.
         * @return array Associative array of supported languages.
         */
        public function supportedLanguages(): array
        {
            return ['sw', 'en'];
        }

        /**
         * Register user defined middlewares. This function provide access for user
         * to register and execute middlewares
         * @param Middleware $mw Middleware object
         * @return void
         */
        public abstract function registerMiddleware(Middleware &$mw): void;

        /**
         * Get or set application modules directory
         * @return string Path relative to application root directory
         */
        public function moduleDir(): string
        {
            return '/modules';
        }

        /**
         * Get or set user defined view directory.
         * @return string Path relative to current module directory
         */
        public function viewDir(): string
        {
            return '/presentation/views';
        }

        /**
         * Get or set user defined language directory. This will be folder on every
         * module where the language files will reside.
         * @return string Path relative to current module directory
         */
        public function languageDir(): string
        {
            return '/presentation/lang';
        }

        /**
         * Get user application name
         * @return string Application name
         */
        public function appName(): string
        {
            return Framework::NAME . ' v' . Framework::VERSION;
        }

        /**
         * Returns application storage directory where all application specific
         * static files are stored. The path must be accessible and writable by
         * the web server
         * @return string Absolute path
         */
        public function appStorage(): string
        {
            return $this->root() . '/.storage';
        }

        /**
         * Returns user group Id shared by one or more users e.g company unique Id.
         * This Id helps accessing and protecting shared resources (e.g uploaded files)
         * against outsiders. This ID should not be changed anyhow, otherwise user will
         * loose access to their shared uploaded files.
         * @return string|null Shared unique id
         */
        public function userGroupId(): ?string
        {
            return null;
        }

        /**
         * Check whether a user has a given group id. user can have multiple
         * group ids
         * @param string $groupId The group Id to check
         * @return bool True if user has the given group id, false otherwise
         */
        public function userGroupIdExists(string $groupId): bool
        {
            return false;
        }

        /**
         * Returns user private unique Id. This Id helps accessing and protecting
         * private resources (e.g uploaded files) against outsiders. This ID should not
         * be changed anyhow, otherwise user will loose access to their private
         * uploaded files.
         * @return string|null Private unique id
         */
        public function getUserPrivateId(): ?string
        {
            return $this->userId;
        }

        /**
         * Get Application protected storage directory for storing static contents.
         * This directory is accessible only by authenticated users.
         * @return string Path to storage directory relative to appStorage()
         * @see self::appStorage()
         */
        public final function appProtectedStorage(): string
        {
            return '/0prt';
        }

        /**
         * Get Application public storage directory for storing static contents.
         * This directory is accessible by everyone.
         * @return string Path to storage directory relative to appStorage()
         * @see self::appStorage()
         */
        public final function appPublicStorage(): string
        {
            return '/1pub';
        }

        /**
         * Default path to homepage if '/' is provided by during HTTP request
         */
        public abstract function homePath(): string;

        /**
         * Returns a list of HTTP request methods supported by the application
         * (in lower case) separated by a comma
         * @see SecurityMiddleware::passedRequestMethodCheck()
         * @see SecurityMiddleware::preflightRequest()
         */
        public function allowedRequestMethods(): string
        {
            return 'get,post,head,put,delete';
        }

        /**
         * Returns a list of HTTP request headers supported by the application
         * (in lower case) separated by a comma
         * @see SecurityMiddleware::passedRequestMethodCheck()
         * @see SecurityMiddleware::preflightRequest()
         */
        public function allowedRequestHeaders(): string
        {
            return '*';
        }

        /**
         * Check whether a given resource is available to both authenticated and
         * unauthenticated users (guests).
         * @return bool True on success, false otherwise.
         */
        public function accessibleByPublic(): bool
        {
            return false;
        }

        /**
         * Check whether a given resource is available only to unauthenticated users.
         * @return bool True on success, false otherwise.
         */
        public function accessibleByGuest(): bool
        {
            return false;
        }

        /**
         * Check if a requesting domain (origin) in allowed to access resource
         * on this server. Usually this is achieved via Origin header which is
         * sent by the web browser.
         * @param string $domain Domain name (FQDN) or IP address
         * @return string
         */
        public function whitelistedDomain(string $domain): bool
        {
            return false;
        }

        /**
         * Whether to send CSP (Content Security Policy) headers or not.
         * @return ContentSecurityPolicy
         */
        public function csp(): ContentSecurityPolicy
        {
            return ContentSecurityPolicy::BASIC;
        }

        /**
         * Tells a web browser how to decide which domain can access resources
         * on this application.
         * @return RespourceAccessPolicy
         * @see SecurityMiddleware::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): ResourceAccessPolicy
        {
            return ResourceAccessPolicy::THIS_DOMAIN_AND_SUBDOMAIN;
        }

        /**
         * Tells a web browser how send HTTP referrer header. This is important
         * for managing user browsing privacy
         * @return BrowsingPrivacy
         */
        public function browsingPrivacy(): BrowsingPrivacy
        {
            return BrowsingPrivacy::THIS_DOMAIN;
        }

        /**
         * Set level for compression algorithm. Default level is DataCompression::BEST
         * @return DataCompression
         */
        public function compressionLevel(): DataCompression
        {
            return DataCompression::BEST;
        }

        /**
         * Set data compression minimum size (in bytes) during transmission.
         * The default size is 1KB
         * @return int Minimum number of bytes to compress
         */
        public function compressionMinSize(): int
        {
            return 1024; //1KB
        }

        /**
         * Get name of the CSRF token used in CSRF protection.
         * @return string
         */
        public function csrfTokenName(): string
        {
            return 'csrf_token';
        }

        /**
         * Enable/disable user authorization on restricted resources.
         * (Permanent skipping authorization is not recommended in production environment)
         * @return bool
         * @see self::getAuthenticationStrategies()
         */
        public function skipAuthentication(): bool
        {
            return false;
        }

        /**
         * Get file storage media where uploaded files will be saved and retrieved.
         * To be able to use remote storage.
         * @return StorageMedia
         */
        public function getStorageMedia(): StorageMedia
        {
            return new LocalStorage($this->app);
        }

        /**
         * Check whether a user is granted access to a resource.
         * @param string $method Request method e.g get, post etc
         * @param RequestRoute $route Request route object
         * @return bool True if a user is granted access, false otherwise.
         */
        public function accessGranted(string $method, RequestRoute $route): bool
        {
            if (empty($this->permissionList)) {
                return false;
            }
            $endpoint = Endpoints::digest($method, $route);
            return str_contains($this->permissionList, $endpoint['hash']);
//            return (preg_match('\b' . $target . '\b', $this->permissionList) === 1);
        }

        /**
         * Return a name of a log file
         * @return string
         */
        public function logFileName(): string
        {
            return date('Y-m-d') . '.log';
        }

        /**
         * Return digital signature used to sign response body, and to verify
         * request body
         * @return DigitalSignature|null Digital signature object
         */
        public function signature(): ?DigitalSignature
        {
            return null;
        }

        /**
         * Return encryption object used to encrypt response body, and to decrypt
         * request body.
         * @return Encryption|null Encryption object
         */
        public function encryption(): ?Encryption
        {
            return null;
        }

        /**
         * Return header name that will hold digital signature. All signature
         * are send via HTTP header.
         * @return string
         */
        public function signatureHeaderName(): string
        {
            return 'X-Signature';
        }

        /**
         * Modify response before sending to user. Example signing a response,
         * encrypting, compressing response body etc
         * @return void
         */
        public function responseMutator(): void
        {
            $this->app->response
                    ->compress($this->compressionMinSize(), $this->compressionLevel())
                    ->sign($this->signature(), $this->signatureHeaderName())
                    ->encrypt($this->encryption());
        }

        /**
         * Modify request before processing it. Example verifying signature,
         * decrypting, decompressing request body etc
         * @return void
         */
        public function requestMutator(): void
        {
            $this->app->request
                    ->decrypt($this->encryption())
                    ->verify($this->signature(), $this->signatureHeaderName())
                    ->decompress();
        }

        /**
         * Run unit and integration test
         */
        public static abstract function runTest(): TestResult;

        /**
         * Get database connection object specified by connection name. If no
         * connection name specified then default connection will be returned
         * @return DatabaseConnection|null Database connection
         */
        public function database(): ?DatabaseConnection
        {
            return null;
        }

        /**
         * Get Oauth 2.0 repository for data exchange.
         * @return Oauth2Repository|null Oauth 2.0 repository object
         */
        public function getOauth2Repository(): ?Oauth2Repository
        {
            return null;
        }
    }

}
