<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\v1\config {

    use shani\engine\core\AutoConfig;
    use shani\engine\http\Middleware;

    final class Settings extends AutoConfig
    {

        public function __construct(\shani\engine\http\App &$app)
        {
            parent::__construct($app);
        }

        public function root(): ?string
        {
            return '/demo/v1';
        }

        public function middleware(Middleware &$mw): void
        {
            \apps\demo\v1\middleware\Register::exec($this->app, $mw);
        }

        public function homepage(): string
        {
            return '/greetings/0/hello/1/world';
        }

        public function handleApplicationErrors(\Throwable $t): void
        {

        }

        public function handleHttpErrors(): void
        {
            $this->app->response()->send();
        }

        public function defaultLanguage(): string
        {
            return 'en';
        }

        public function storageDir(): string
        {
            return '/demo/storage';
        }
    }

}
