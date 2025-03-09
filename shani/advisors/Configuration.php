<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\advisors {

    use library\DataCompressionLevel;
    use shani\core\Framework;
    use shani\http\App;
    use shani\http\Middleware;

    abstract class Configuration
    {

        /**
         *  allows resource access on this application from this domain only
         */
        public const ACCESS_POLICY_THIS_DOMAIN = 0;

        /**
         *  allows resource access on this application from this domain and it's subdomain
         */
        public const ACCESS_POLICY_THIS_DOMAIN_AND_SUBDOMAIN = 1;

        /**
         *  allows resource access on this application from any domain (Not recommended)
         */
        public const ACCESS_POLICY_ANY_DOMAIN = 2;

        /**
         *  Do not use resource access policy (Not recommended)
         */
        public const ACCESS_POLICY_DISABLE = 3;

        /**
         * Never send the Referrer header (Protect user's privacy)
         */
        public const BROWSING_PRIVACY_STRICT = 0;

        /**
         * Send the Referrer header (See what user is browsing but only on this domain)
         */
        public const BROWSING_PRIVACY_THIS_DOMAIN = 1;

        /**
         * Send the Referrer header (i.e see what user is browsing on all domains
         * but do not show the actual content they browse)
         */
        public const BROWSING_PRIVACY_PARTIALLY = 2;

        /**
         * Send the full Referrer header on same-origin requests and only the
         * URL without the path on cross-origin requests
         */
        public const BROWSING_PRIVACY_NONE = 3;

        /**
         * Whether the current user is authenticated and has at least one permission
         * @var bool
         */
        public readonly bool $authenticated;
        protected readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->authenticated = $this->userPermissions() !== null;
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
         * Get or set cookie max age before expiration.
         * @return string A date/time string. Valid formats are explained in Date and Time Formats.
         */
        public function cookieMaxAge(): string
        {
            return '2 hours';
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
            return true;
        }

        /**
         * Get/set request methods that will be protected from CSRF attacks
         * @return array Methods (in lower cases) to protect.
         */
        public function csrfProtectedMethods(): array
        {
            return ['post', 'put', 'patch', 'delete'];
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
        public function errorHandler(\Throwable $t = null): void
        {
            $this->app->send();
        }

        /**
         * Set all application supported languages where key being language code
         * and value being language name.
         * @return array Associative array of supported languages.
         */
        public function languages(): array
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
         * Returns application web root directory where all application specific static files are stored.
         * @return string Path relative to application directory
         */
        public abstract function staticAssetStorage(): string;

        /**
         * All files that have to be accessed by an authenticated users are kept here.
         * @return string|null A path relative to a web root directory
         */
        public function protectedStorage(): ?string
        {
            return '/protected';
        }

        /**
         * Default path to homepage if '/' is provided by during HTTP request
         */
        public abstract function homepage(): string;

        /**
         * Returns an array of HTTP request methods supported by the application (in lower case)
         * @see SecurityMiddleware::passedRequestMethodCheck()
         */
        public abstract function requestMethods(): array;

        /**
         * Get a list of authenticated user's permissions separated by comma.
         * @return string|null List of user permissions or null if no permission is granted
         * @see App::hasAuthority()
         */
        public abstract function userPermissions(): ?string;

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
         * @return int
         * @see SecurityMiddleware::resourceAccessPolicy()
         */
        public function resourceAccessPolicy(): int
        {
            return self::ACCESS_POLICY_THIS_DOMAIN_AND_SUBDOMAIN;
        }

        /**
         * Tells a web browser how send HTTP referrer header. This is important
         * for managing user browsing privacy
         * @return int
         */
        public function browsingPrivacy(): int
        {
            return self::BROWSING_PRIVACY_THIS_DOMAIN;
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
         * Enable/disable preflight request sent by the browser
         * @return bool
         * @see SecurityMiddleware::preflightRequest()
         */
        public function preflightRequest(): bool
        {
            return true;
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
        public function authorizationDisabled(): bool
        {
            return true;
        }
    }

}
