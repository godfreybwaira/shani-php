<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\codes\v2\config {

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
            return '/demo/codes/v2';
        }

        public function middleware(Middleware &$mw): void
        {
            \apps\demo\codes\v2\middleware\Register::exec($this->app, $mw);
        }

        public function homepage(): string
        {
            return '/components/0/generator';
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

        public function assetDir(): ?string
        {
            return '/demo/asset';
        }
    }

}
