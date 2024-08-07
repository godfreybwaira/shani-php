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

        public function handleHttpErrors(?string $errorMessage = null): void
        {
            $this->app->response()->send();
        }

        public function defaultLanguage(): string
        {
            return 'en';
        }

        public function webroot(): string
        {
            return '/demo/storage';
        }

        public function requestMethods(): array
        {
            return ['get', 'post', 'head'];
        }
    }

}
