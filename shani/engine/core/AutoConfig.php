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

        public function requestMethodsDir(): string
        {
            return '/src';
        }

        public function csrf(): int
        {
            return self::CSRF_FLEXIBLE;
        }

        public function defaultLanguage(): string
        {
            return 'sw';
        }

        public abstract function fallbackUrl(): string;

        public function languages(): array
        {
            return ['sw' => 'Kiswahili', 'en' => 'English'];
        }

        public abstract function middleware(Register &$mw): void;

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

        public abstract function signatureSecretKey(): string;

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
