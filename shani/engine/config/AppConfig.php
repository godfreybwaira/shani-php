<?php

/**
 * Description of AppConfig
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:05:46 PM
 */

namespace shani\engine\config {

    interface AppConfig
    {

        public function cookieMaxAge();

        public function csrf(): int;

        public function root(): string;

        public function languageDefault(): string;

        public function sessionName(): string;

        public function languages(): array;

        public function languageDir(): string;

        public function moduleDir(): string;

        public function sourceDir(): string;

        public function middleware(\shani\engine\middleware\Register &$mw);

        public function viewDir(): string;

        public function fallback(): string;

        public function templateVersion(): string;

        public function breadcrumbDir(): string;

        public function breadcrumbMethodsDir(): string;

        public function modulePublic(): array;

        public function moduleGuest(): array;

        public function homeGuest(): string;

        public function homeAuth(): string;

        public function appName(): string;

        public function development(): bool;
    }

}
