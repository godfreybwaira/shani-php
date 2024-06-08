<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\test\codes\v1\config {

    use shani\engine\config\AppConfig;
    use shani\engine\middleware\Register;

    final class Settings implements AppConfig
    {

        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App &$app)
        {
            $this->app = $app;
        }

        /**
         * <p>Get the application root directory with trailing /</p>
         * @return string Return application root directory relative to App directory
         */
        public function root(): string
        {
            return App::ROOT_DIR;
        }

        public function sessionName(): string
        {
            return App::SESSION_NAME;
        }

        public function cookieMaxAge()
        {
            return App::COOKIE_MAX_AGE;
        }

        public function sourceDir(): string
        {
            return Path::SOURCE;
        }

        public function csrf(): int
        {
            return App::CSRF;
        }

        public function languageDefault(): string
        {
            return App::LANGUAGE_DEFAULT;
        }

        public function fallback(): string
        {
            return Module::FALLBACK;
        }

        public function languages(): array
        {
            return App::LANGUAGES;
        }

        public function middleware(Register &$mw)
        {
            \apps\test\codes\v1\middleware\Register::exec($this->app, $mw);
        }

        public function moduleDir(): string
        {
            return Path::MODULES;
        }

        public function breadcrumbDir(): string
        {
            return Path::BREADCRUMB;
        }

        public function breadcrumbMethodsDir(): string
        {
            return Path::BREADCRUMB_METHOD;
        }

        public function viewDir(): string
        {
            return Path::VIEWS;
        }

        public function languageDir(): string
        {
            return Path::LANGUAGE;
        }

        public function appName(): string
        {
            return App::NAME;
        }

        public function appDescription(): ?string
        {
            return App::DESCRIPTION;
        }

        public function assetDir(): ?string
        {
            return App::ASSET_DIR;
        }

        public function signatureSecretKey(): string
        {
            return App::JWT_SECRET_KEY;
        }

        public function homeGuest(): string
        {
            return Module::HOME_GUEST;
        }

        public function homeAuth(): string
        {
            return Module::HOME_AUTH;
        }

        public function moduleGuest(): array
        {
            return Module::GUESTS;
        }

        public function modulePublic(): array
        {
            return Module::PUBS;
        }

        public function authorizationType(): int
        {
            return App::AUTHORIZATION;
        }

        public function templateVersion(): string
        {
            return App::TEMPLATE_VERSION;
        }
    }

}
