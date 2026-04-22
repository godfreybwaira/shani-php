<?php

/**
 * Description of Settings
 * @author coder
 *
 * Created on: Feb 18, 2024 at 2:20:26 PM
 */

namespace apps\demo\config {

    use apps\demo\middleware\Oauth2Client;
    use apps\demo\middleware\Test;
    use features\oauth2\Oauth2Repository;
    use features\persistence\DatabaseDriver;
    use features\persistence\DatabaseInterface;
    use features\persistence\SQLDatabase;
    use features\session\SessionConnectionInterface;
    use features\test\helpers\TestCategory;
    use features\test\helpers\TestSeverity;
    use features\test\TestCase;
    use features\test\TestGroup;
    use features\test\TestResult;
    use features\utils\HttpClient;
    use features\utils\URI;
    use shani\advisors\Configuration;
    use shani\http\enums\HttpStatus;
    use shani\http\Middleware;
    use shani\http\ResponseEntity;
    use shani\launcher\App;
    use shani\launcher\Framework;

    final class Settings extends Configuration
    {

        public function __construct(App $app)
        {
            parent::__construct($app);
        }

        public function root(): string
        {
            return Framework::DIR_APPS . '/demo';
        }

        public function homePath(): string
        {
            return '/shani/0/components/0/index';
        }

        public function allowedRequestMethods(): string
        {
            return 'get,post,head,put';
        }

        public function registerMiddleware(Middleware $mw): void
        {
            $mw->on('before', fn() => Test::m1($this->app));
            $mw->on('before', fn() => Test::m2($this->app));
        }

        public function skipCsrfTest(): bool
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

        public function accessingPublicResource(): bool
        {
            return in_array($this->app->request->route()->module, ['shani', 'security']);
        }

        public static function runTest(): TestResult
        {
            $result = new TestResult('UAT for my application', location: Framework::DIR_SERVER_STORAGE);
            $g1 = new TestGroup('MY FIRST MODULE');
            $g2 = new TestGroup('MY SECOND MODULE');
            $g3 = new TestGroup('MY THIRD MODULE');
            $caseg11 = new TestCase(TestSeverity::LOW, 'TEST_001');
            $caseg12 = new TestCase(TestSeverity::LOW, 'TEST_002');
            $caseg13 = new TestCase(TestSeverity::HIGH, 'TEST_003');
            $caseg11->test('Running test 001', fn() => 1 == 1);
            $caseg12->test('Running test 002', fn() => 1 == 1);
            $caseg13->test('Running test 003', fn() => 1 == 2);
            $g1->addCase($caseg11, $caseg12, $caseg13);
            ///////////////////////////////////////////
            $caseg21 = new TestCase(TestSeverity::HIGH, 'TEST_004');
            $caseg22 = new TestCase(TestSeverity::MEDIUM, 'TEST_005');
            $caseg23 = new TestCase(TestSeverity::LOW, 'TEST_006');
            $caseg21->test('Running test 004', fn() => 1 == 2);
            $caseg22->test('Running test 005', fn() => 1 == 1);
            $caseg23->test('Running test 006', fn() => 1 == 2);
            $g2->addCase($caseg21, $caseg22, $caseg23);
            ///////////////////////////////////////////
            $caseg31 = new TestCase(TestSeverity::HIGH, 'TEST_007');
            $caseg32 = new TestCase(TestSeverity::MEDIUM, 'TEST_008');
            $caseg33 = new TestCase(TestSeverity::HIGH, 'TEST_009', TestCategory::PERFORMANCE);
            $caseg31->test('Test if 2+2=4', fn() => 2 + 2 === 4);
            $caseg32->test('Test if A is same as a', fn() => 'A' === 'a');
            $caseg33->test('Testing if I can make a call on this server', function () {
                $client = new HttpClient(new URI('https://jsonplaceholder.typicode.com'));
                $client->enableAsync(false)->enableSSLVerification(false);
                $code = null;
                $client->get('/users', function (ResponseEntity $res)use (&$code) {
                    $code = $res->status();
                });
                return $code === HttpStatus::OK;
            }, maxExecutionTime: 7, iterations: 1);
            $g3->addCase($caseg31, $caseg32, $caseg33);
            $result->addGroup($g1, $g2, $g3);
            return $result;
        }

        public function getDatabase(): ?DatabaseInterface
        {
            return new SQLDatabase(DatabaseDriver::MYSQL, 'test', 'localhost', 3306, 'testuser', 'test123');
        }

        public function getOauth2Repository(): Oauth2Repository
        {
            return new Oauth2Client();
        }

        public function getAuthenticationStrategies(): array
        {
            return [
                new auth\PasswordAuthenticator($this->app),
                new auth\JwtAuthenticator($this->app),
            ];
        }

        public function getSessionConnection(): ?SessionConnectionInterface
        {
            return new \features\session\dto\RedisConnectionDto('localhost', 6379);
        }
    }

}
