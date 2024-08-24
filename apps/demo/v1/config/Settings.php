<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\v1\config {

    use shani\advisors\Configuration;
    use shani\engine\http\App;
    use shani\engine\http\Middleware;

    final class Settings extends Configuration
    {

        public function __construct(App &$app, array &$configurations)
        {
            parent::__construct($app, $configurations);
        }

        public function root(): ?string
        {
            return '/demo/v1';
        }

        public function homepage(): string
        {
            return '/greetings/0/hello/1/world';
        }

        public function httpErrorHandler(?string $errorMessage = null): void
        {
            $this->app->response()->send();
        }

        public function webroot(): string
        {
            return '/demo/storage';
        }

        public function requestMethods(): array
        {
            return ['get', 'post', 'head'];
        }

        public function middleware(Middleware &$mw): ?\shani\advisors\SecurityMiddleware
        {
            return new \apps\demo\v1\middleware\Register($this->app, $mw);
        }

        public function userPermissions(): ?string
        {
            return null;
        }
    }

}
