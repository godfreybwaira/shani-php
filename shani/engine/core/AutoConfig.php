<?php

/**
 * User predefined configuration class. All these methods are intended to be
 * overridden, otherwise the defaults will be assumed.
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\engine\core {

    abstract class AutoConfig
    {

        /**
         * Turn OFF CSRF signature verification.
         */
        public const CSRF_OFF = 0;

        /**
         * Same CSRF signature is used on same form just once. Once the signature
         * is verified the form becomes invalid on next use. This is the default
         * CSRF protection mechanism.
         */
        public const CSRF_STRICT = 1;

        /**
         * Different CSRF signature is used for the same form, each form submit
         * comes with it's own CSRF signature.
         */
        public const CSRF_FLEXIBLE = 2;

        protected \shani\engine\http\App $app;

        protected function __construct(\shani\engine\http\App &$app)
        {
            $this->app = $app;
        }

        /**
         * Get the application root directory with trailing /
         * @return string Application root directory relative to Apps directory
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
         * Set directory inside application module where module controllers with
         * be resides.
         * <p>It is in this directory you will create GET, POST, PUT, DELETE
         * or any other custom http method directories, These directories must be
         * in lowercase.</p>
         * @return string Path relative to current module directory
         */
        public function requestMethodsDir(): string
        {
            return '/web';
        }

        /**
         * Get or set user defined CSRF protection mechanism.
         * @return int CSRF protection mechanism.
         */
        public function csrf(): int
        {
            return self::CSRF_STRICT;
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
         * Handle all HTTP errors that may occur during program execution.
         * @param string|null $errorMessage HTTP error message
         */
        public abstract function handleHttpErrors(?string $errorMessage = null): void;

        /**
         * Handle all application errors
         * @param \Throwable $t
         * @return void
         */
        public function handleApplicationErrors(\Throwable $t): void
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
         */
        public abstract function middleware(\shani\engine\http\Middleware &$mw): void;

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
            return Framework::NAME . ' v' . Framework::VERSION;
        }

        /**
         * Get user application storage directory.
         * @return string Path relative to application directory
         */
        public abstract function storageDir(): string;

        /**
         * Default path to homepage if '/' is provided by during HTTP request
         */
        public abstract function homepage(): string;

        /**
         * Returns an array of HTTP request methods supported by the application (in lower case)
         */
        public abstract function requestMethods(): array;
    }

}
