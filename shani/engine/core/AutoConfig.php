<?php

/**
 * Description of AutoConfig
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\engine\core {

    abstract class AutoConfig
    {

        public const CSRF_OFF = 0;
        public const CSRF_STRICT = 1;
        public const CSRF_FLEXIBLE = 2;

        protected \shani\engine\http\App $app;

        protected function __construct(\shani\engine\http\App &$app)
        {
            $this->app = $app;
        }

        /**
         * <p>Get the application root directory with trailing /</p>
         * @return string Return application root directory relative to App directory
         */
        public abstract function root(): string;

        public function sessionName(): string
        {
            return 'sessionId';
        }

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
         * @return string Directory name
         */
        public function requestMethodsDir(): string
        {
            return '/src';
        }

        public function csrf(): int
        {
            return self::CSRF_FLEXIBLE;
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
         * Set relative URL of application to be used for error handling.
         * This application should be able to handle all HTTP errors depending
         * on status code provided.
         * @return string|null URL to application resource
         */
        public function fallbackUrl(): ?string
        {
            return null;
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
         * Execute user defined middlewares
         */
        public abstract function middleware(\shani\engine\middleware\Register &$mw): void;

        public function moduleDir(): string
        {
            return '/modules';
        }

        public function breadcrumbDir(): string
        {
            return '/breadcrumb';
        }

        public function breadcrumbMethodsDir(): string
        {
            return '/functions';
        }

        public function viewDir(): string
        {
            return '/views';
        }

        public function languageDir(): string
        {
            return '/lang';
        }

        public function appName(): string
        {
            return 'Shani Foundation Framework v1.0';
        }

        public function appDescription(): ?string
        {
            return null;
        }

        public abstract function assetDir(): ?string;

        public function signatureSecretKey(): string
        {
            return '';
        }

        public abstract function homepage(): string;

        public function guestModules(): array
        {
            return [];
        }

        public function publicModules(): array
        {
            return [];
        }

        public function authorizationType(): int
        {
            return \shani\engine\authorization\Authorization::AUTH_SESSION;
        }

        public abstract function templateVersion(): string;
    }

}
