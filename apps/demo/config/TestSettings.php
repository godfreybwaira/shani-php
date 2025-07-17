<?php

/**
 * Description of TestSettings
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
    use test\TestResult;

    final class TestSettings extends Configuration
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
//            $result = new TestResult(location: SERVER_ROOT);
//            $case1 = new \test\TestCase('My first test');
//            $case2 = new \test\TestCase('My second test');
//            $case3 = new \test\TestCase('Real test...');
//            $case1->test('Test if 2+2=4', fn() => 2 + 2 === 4);
//            $case2->test('Test if A is same as a', fn() => 'A' === 'a');
//            $case3->test('Testing if I can make a call on this server', function () {
//                $client = new \lib\client\HttpClient(new \lib\URI('https://dev.shani.v2.local'));
//                $client->enableAsync(false)->enableSSLVerification(false);
//                $code = null;
//                $client->get('/', function (\lib\http\ResponseEntity $res)use (&$code) {
//                    $code = $res->status();
//                });
//                return $code === \lib\http\HttpStatus::OK;
//            });
//            $result->addCase($case1, $case2, $case3);
//            return $result;
        }

        public function database(string $connName = null): Database
        {

        }
    }

}
