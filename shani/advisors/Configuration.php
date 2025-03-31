<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\advisors {

    use lib\Duration;
    use shani\http\App;
    use shani\core\Framework;
    use shani\http\Middleware;
    use lib\DataCompressionLevel;
    use shani\contracts\StorageMedia;
    use shani\persistence\LocalStorage;
    use shani\advisors\web\AccessPolicy;
    use shani\advisors\web\BrowsingPrivacy;

    abstract class Configuration
    {

        /**
         * Whether the current user is authenticated and has at least one permission
         * @var bool
         */
        public readonly bool $authenticated;
        public readonly ?string $permissionList;
        protected readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->permissionList = $this->clientPermissions();
            $this->authenticated = $this->permissionList !== null;
        }

        /**
         * Get the application root directory with a leading /. If set to null
         * then the application root directory will be the /apps directory
         * @return string|null Application root directory relative to Apps directory
         */
        public function root(): ?string
        {
            return null;
        }

        /**
         * Set or get Session cookie name.
         * @return string Cookie name
         */
        public function sessionName(): string
        {
            return 'sessionId';
        }

        /**
         * The directory where session data will be saved. This directory must be
         * writable and exists
         * @return string
         */
        public function sessionSavePath(): string
        {
            return sys_get_temp_dir();
        }

        /**
         * Whether session engine is enabled or not. If true then session data
         * will be persisted on session storage, otherwise session object will
         * behave just like normal object ait it's data will not be persisted.
         * @return bool
         */
        public function sessionEnabled(): bool
        {
            return $this->app->platform() === 'web';
        }

        /**
         * Get or set cookie max age before expiration.
         * @return \DateTimeInterface A date/time object.
         */
        public function cookieMaxAge(): \DateTimeInterface
        {
            return Duration::of(2, Duration::HOURS);
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
         * @return bool True to enable, to disable otherwise.
         */
        public function csrfProtectionEnabled(): bool
        {
            return false;
        }

        /**
         * Get/set request methods that will be protected from CSRF attacks
         * @return bool True if request Methods (in lower cases) to protect.
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
         * Handle all application errors
         * @param \Throwable $t
         * @return void
         */
        public function errorHandler(\Throwable $t): void
        {
            echo 'Message: ' . $t->getMessage() . PHP_EOL;
            echo 'File: ' . $t->getFile() . ':' . $t->getLine() . PHP_EOL;
            echo 'Cause: ' . PHP_EOL . $t->getTraceAsString();
            $this->app->send();
        }

        /**
         * When app running state is false and your application will handle the
         * situation then return true, otherwise return false
         * @return bool
         */
        public function appNotRunningHandled(): bool
        {
            return false;
        }

        /**
         * Get all application supported languages where a key is a language code
         * and a value is a language name.
         * @return array Associative array of supported languages.
         */
        public function supportedLanguages(): array
        {
            return ['sw' => 'Kiswahili', 'en' => 'English'];
        }

        /**
         * Execute user defined middlewares. This function provide access for user
         * to register and execute middlewares
         * @param Middleware $mw Middleware object
         * @return SecurityMiddleware
         */
        public abstract function middleware(Middleware &$mw): SecurityMiddleware;

        /**
         * Get or set application modules directory
         * @return string Path relative to application root directory
         */
        public function moduleDir(): string
        {
            return '/modules';
        }

        /**
         * Get a user defined breadcrumb directory.
         * @return string Path relative to current module directory
         */
        public abstract function breadcrumbDir(): string;

        /**
         * Get or set user defined breadcrumb methods directory.
         * @return string Path relative to current breadcrumb directory
         */
        public abstract function breadcrumbMethodsDir(): string;

        /**
         * Get or set user defined view directory.
         * @return string Path relative to current module directory
         */
        public abstract function viewDir(): string;

        /**
         * Get or set user defined language directory. This will be folder on every
         * module where the language files will reside.
         * @return string Path relative to current module directory
         */
        public abstract function languageDir(): string;

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
        public abstract function appStorage(): string;

        /**
         * Returns client group Id shared by one or more clients e.g company unique Id.
         * This Id helps protecting shared resources (e.g uploaded files) against outsiders.
         * @return string|null Shared unique id
         */
        public function clientGroupId(): ?string
        {
            return null;
        }

        /**
         * Get Application protected storage directory for storing static contents.
         * This directory is accessible only by authenticated clients.
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
        public abstract function home(): string;

        /**
         * Returns a list of HTTP request methods supported by the application
         * (in lower case) separated by a comma
         * @see SecurityMiddleware::passedRequestMethodCheck()
         * @see SecurityMiddleware::preflightRequest()
         */
        public abstract function allowedRequestMethods(): string;

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
         * Get a list of authenticated client's permissions separated by a comma.
         * Application must set permission using this function after a client is being
         * logged in successfully.
         * @return string|null List of user permissions or null if no permission is granted
         * @see App::accessGranted()
         */
        public abstract function clientPermissions(): ?string;

        /**
         * Check whether a given module is available among modules granted access to a public
         * @param string $module Module name
         * @return bool True on success, false otherwise.
         */
        public function publicModule(string $module): bool
        {
            return false;
        }

        /**
         * Check whether a given module is available among modules granted access to a guest user
         * @param string $module Module name
         * @return bool True on success, false otherwise.
         */
        public function guestModule(string $module): bool
        {
            return false;
        }

        /**
         * Returns a list of domains (FQDN), ip address or subdomains that a web browser
         * will allow to access resources on this application. The list is separated by comma
         * @return string
         */
        public function whitelistedDomains(): string
        {
            return '*';
        }

        /**
         * Tells a web browser how to decide which domain can access resources
         * on this application.
         * @return AccessPolicy
         * @see SecurityMiddleware::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): AccessPolicy
        {
            return AccessPolicy::THIS_DOMAIN_AND_SUBDOMAIN;
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
         * Set level for compression algorithm. Default level is DataCompressionLevel::BEST
         * @return DataCompressionLevel
         */
        public function compressionLevel(): DataCompressionLevel
        {
            return DataCompressionLevel::BEST;
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
         * (disabling is not recommended in production environment)
         * @return bool
         */
        public function authorizationEnabled(): bool
        {
            return false;
        }

        /**
         * Get file storage media where uploaded files will be saved and retrieved.
         * To be able to use remote storage.
         * @param string $media Storage media
         * @return StorageMedia
         */
        public function getStorageMedia(string $media): StorageMedia
        {
            return new LocalStorage($this->app);
        }
    }

}
