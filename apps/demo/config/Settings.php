<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\config {

    use apps\demo\middleware\Register;
    use shani\advisors\Configuration;
    use shani\advisors\SecurityMiddleware;
    use shani\http\App;
    use shani\http\Middleware;

    final class Settings extends Configuration
    {

        public function __construct(App &$app)
        {
            parent::__construct($app);
        }

        public function root(): ?string
        {
            return '/demo';
        }

        public function homepage(): string
        {
            return '/greetings/0/hello/1/world';
        }

        public function appStorage(): string
        {
            return '/demo/storage';
        }

        public function requestMethods(): array
        {
            return ['get', 'post', 'head'];
        }

        public function middleware(Middleware &$mw): SecurityMiddleware
        {
            return new Register($this->app, $mw);
        }

        public function userPermissions(): ?string
        {
            return null;
        }

        public function breadcrumbDir(): string
        {
            return '/presentation/breadcrumb';
        }

        public function breadcrumbMethodsDir(): string
        {
            return '/function';
        }

        public function languageDir(): string
        {
            return '/presentation/lang';
        }

        public function viewDir(): string
        {
            return '/presentation/views';
        }

        public function isAsync(): bool
        {
            return $this->app->request->header()->get('X-Request-Mode') === 'async';
        }
    }

}
