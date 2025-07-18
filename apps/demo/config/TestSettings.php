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
            $result = new TestResult('UAT for my application', location: '/home/goddy/Desktop');
            $g1 = new \test\TestGroup('MY FIRST MODULE');
            $g2 = new \test\TestGroup('MY SECOND MODULE');
            $g3 = new \test\TestGroup('MY THIRD MODULE');
            $caseg11 = new \test\TestCase(\test\TestSeverity::LOW, 'TEST_001');
            $caseg12 = new \test\TestCase(\test\TestSeverity::LOW, 'TEST_002');
            $caseg13 = new \test\TestCase(\test\TestSeverity::HIGH, 'TEST_003');
            $caseg11->test('Running test 001', fn() => 1 == 1);
            $caseg12->test('Running test 002', fn() => 1 == 1);
            $caseg13->test('Running test 003', fn() => 1 == 2);
            $g1->addCase($caseg11, $caseg12, $caseg13);
            ///////////////////////////////////////////
            $caseg21 = new \test\TestCase(\test\TestSeverity::HIGH, 'TEST_004');
            $caseg22 = new \test\TestCase(\test\TestSeverity::MEDIUM, 'TEST_005');
            $caseg23 = new \test\TestCase(\test\TestSeverity::LOW, 'TEST_006');
            $caseg21->test('Running test 004', fn() => 1 == 2);
            $caseg22->test('Running test 005', fn() => 1 == 1);
            $caseg23->test('Running test 006', fn() => 1 == 2);
            $g2->addCase($caseg21, $caseg22, $caseg23);
            ///////////////////////////////////////////
            $caseg31 = new \test\TestCase(\test\TestSeverity::HIGH, 'TEST_007');
            $caseg32 = new \test\TestCase(\test\TestSeverity::MEDIUM, 'TEST_008');
            $caseg33 = new \test\TestCase(\test\TestSeverity::HIGH, 'TEST_009');
            $caseg31->test('Test if 2+2=4', fn() => 2 + 2 === 4);
            $caseg32->test('Test if A is same as a', fn() => 'A' === 'a');
            $caseg33->test('Testing if I can make a call on this server', function () {
                $client = new \lib\client\HttpClient(new \lib\URI('https://dev.shani.v2.local'));
                $client->enableAsync(false)->enableSSLVerification(false);
                $code = null;
                $client->get('/', function (\lib\http\ResponseEntity $res)use (&$code) {
                    $code = $res->status();
                });
                return $code === \lib\http\HttpStatus::OK;
            });
            $g3->addCase($caseg31, $caseg32, $caseg33);
            $result->addGroup($g1, $g2, $g3);
            return $result;
        }

        public function database(string $connName = null): Database
        {

        }
    }

}
