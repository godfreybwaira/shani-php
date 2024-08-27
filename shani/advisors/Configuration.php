<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\advisors {

    use shani\engine\http\App;
    use shani\engine\http\Middleware;

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

        protected App $app;
        private array $config;

        protected function __construct(App &$app, array &$configurations)
        {
            $this->app = $app;
            $this->config = $configurations;
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
        public function cookieMaxAge()
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
            return '/web';
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
            return $this->config['DEFAULT_LANGUAGE'];
        }

        /**
         * Check whether application is in running state or not. A programmer should
         * implements the logic on application running state, otherwise this configuration
         * has no effect.
         * @return string Application language
         */
        public final function running(): bool
        {
            return $this->config['RUNNING'];
        }

        /**
         * Get application environments
         * @return array application environments
         */
        public final function environments(): array
        {
            return array_keys($this->config['ENVIRONMENTS']);
        }

        /**
         * Get current active application environment
         * @return string application environment
         */
        public final function activeEnvironment(): string
        {
            return $this->config['ACTIVE_ENVIRONMENT'];
        }

        /**
         * Handle all HTTP errors that may occur during program execution.
         * @param string|null $errorMessage HTTP error message
         */
        public abstract function httpErrorHandler(?string $errorMessage = null): void;

        /**
         * Handle all application errors
         * @param \Throwable $t
         * @return void
         */
        public function applicationErrorHandler(\Throwable $t): void
        {
            print_r($t);
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
         * Get or set user defined breadcrumb directory.
         * @return string Path relative to current module directory
         */
        public function breadcrumbDir(): string
        {
            return '/breadcrumb';
        }

        /**
         * Get or set user defined breadcrumb methods directory.
         * @return string Path relative to current breadcrumb directory
         */
        public function breadcrumbMethodsDir(): string
        {
            return '/functions';
        }

        /**
         * Get or set user defined view directory.
         * @return string Path relative to current module directory
         */
        public function viewDir(): string
        {
            return '/views';
        }

        /**
         * Get or set user defined language directory. This will be folder on every
         * module where the language files will reside.
         * @return string Path relative to current module directory
         */
        public function languageDir(): string
        {
            return '/lang';
        }

        /**
         * Set and get user application name
         * @return string Application name
         */
        public function appName(): string
        {
            return \shani\engine\core\Framework::NAME . ' v' . \shani\engine\core\Framework::VERSION;
        }

        /**
         * Returns application web root directory where all application specific static files are stored.
         * @return string Path relative to application directory
         */
        public abstract function webroot(): string;

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
         * Get a list of modules accessible by all users (guests & authenticated)
         * @return array List of public modules
         */
        public function publicModules(): array
        {
            return [];
        }

        /**
         * Get a list of modules accessible by guest users only
         * @return array List of guest modules
         */
        public function guestModules(): array
        {
            return [];
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
         * Set level for compression algorithm from 0 to 9 where 0 = disable,
         * 4-6 = ideal level, 9 = Maximum compression but slow. The default ratio is 5
         * @return int
         */
        public function compressionLevel(): int
        {
            return 5;
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
         * Enable/disable security checks (disabling is not recommended in production environment)
         * @return bool
         * @see SecurityMiddleware::disabled()
         */
        public function disableSecurityAdvisor(): bool
        {
            return false;
        }
    }

}
