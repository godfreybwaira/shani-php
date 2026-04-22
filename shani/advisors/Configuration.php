<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\advisors {

    use features\authentication\UserDetailsDto;
    use features\crypto\DigitalSignature;
    use features\crypto\Encryption;
    use features\logging\LoggingLevel;
    use features\oauth2\Oauth2Repository;
    use features\persistence\DatabaseInterface;
    use features\session\SessionConnectionInterface;
    use features\storage\LocalStorage;
    use features\storage\StorageMediaInterface;
    use features\test\TestResult;
    use features\utils\DataCompression;
    use shani\advisors\web\BrowsingPrivacy;
    use shani\advisors\web\ContentSecurityPolicy;
    use shani\advisors\web\ResourceAccessPolicy;
    use shani\assets\StaticAssetServers;
    use shani\http\Middleware;
    use shani\launcher\App;
    use shani\launcher\Framework;

    abstract class Configuration
    {

        protected readonly App $app;

        protected function __construct(App $app)
        {
            $this->app = $app;
        }

        /**
         * Get authentication strategies objects that implements <code>AuthenticationStrategy</code>.
         * Developer must implement this interface to provide Authentication logic.
         * @return array A list of AuthenticationStrategy objects
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
            return new \features\session\dto\FileConnectionDto();
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
         * Enable/disable CSRF protection by checking on request method.
         * @return bool True to skip, false otherwise.
         */
        public function skipCsrfTest(): bool
        {
            return !str_contains('post,put,patch,delete', $this->app->request->method);
        }

        /**
         * Enable/disable CSRF protection.
         * @return bool True to enable, false otherwise (not recommended on production)
         */
        public function enableCsrfProtection(): bool
        {
            return true;
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
            $this->app->logger()->log(LoggingLevel::ERROR, $t->getMessage());
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
        public abstract function registerMiddleware(Middleware $mw): void;

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
            return $this->root() . '/.bucket';
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
            return 'get,post,head,put,patch,options,delete';
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
        public function accessingPublicResource(): bool
        {
            return false;
        }

        /**
         * Check whether a given resource is available only to unauthenticated users.
         * @return bool True on success, false otherwise.
         */
        public function accessingGuestResource(): bool
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
         * Get name of the CSRF token used in CSRF protection. CSRF token is placed on
         * HTTP request header. Every time the client make a request must provide this header,
         * otherwise the request will be rejected.
         * @return string
         */
        public function csrfTokenName(): string
        {
            return 'X-Csrf-Token';
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
         * @return StorageMediaInterface
         */
        public function getStorageMedia(): StorageMediaInterface
        {
            return new LocalStorage($this->app);
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
         * @return DatabaseInterface|null Database object
         */
        public function getDatabase(): ?DatabaseInterface
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

        /**
         * Select your preferred static asset server.
         * @return StaticAssetServers
         */
        public function getStaticAssetServer(): StaticAssetServers
        {
            return StaticAssetServers::SHANI;
        }
    }

}
