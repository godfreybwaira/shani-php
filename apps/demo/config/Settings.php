<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\config {

    use apps\demo\middleware\Test;
    use shani\advisors\Configuration;
    use shani\core\Framework;
    use shani\http\App;
    use shani\http\Middleware;
    use shani\persistence\Database;
    use shani\persistence\DatabaseDriver;
    use test\TestResult;

    final class Settings extends Configuration
    {

        public function __construct(App &$app)
        {
            parent::__construct($app);
        }

        public function root(): string
        {
            return '/demo';
        }

        public function home(): string
        {
            return '/schools/0/students/1/index';
        }

        public function appStorage(): string
        {
            return Framework::DIR_APPS . '/demo/storage';
        }

        public function allowedRequestMethods(): string
        {
            return 'get,post,head,put';
        }

        public function registerMiddleware(Middleware &$mw): void
        {
            $mw->on('before', fn() => Test::m1($this->app));
            $mw->on('before', fn() => Test::m2($this->app));
        }

        public function clientPermissions(): ?string
        {
            return null;
        }

        public function skipCsrfProtection(): bool
        {
            return true;
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
            return $this->app->request->header()->getOne('X-Request-Mode') === 'async';
        }

        public function accessibleByPublic(): bool
        {
            return in_array($this->app->request->route()->module, ['/schools']);
        }

        public static function runTest(): TestResult
        {

        }

        public function database(string $connName = null): Database
        {
            return new Database(DatabaseDriver::MYSQL, 'test', 'localhost', 3306, 'testuser', 'test123');
        }
    }

}
