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
    use shani\core\Definitions;
    use shani\http\App;
    use shani\http\Middleware;
    use test\TestCase;
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
            return '/greetings/0/hello/1/world';
        }

        public function appStorage(): string
        {
            return Definitions::DIR_APPS . '/demo/storage';
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
            return $this->app->request->header()->getOne('X-Request-Mode') === 'async';
        }

        public function accessibleByPublic(): bool
        {
            return in_array($this->app->request->route()->module, ['/greetings']);
        }

        public static function runTest(): TestResult
        {
            $client = new \lib\client\HttpClient(new \lib\URI('http://localhost:8008/'));
            $client->enableAsync(false);
            $result = new TestResult('Testing My app');
            $result->saveTo('/home/coder/Desktop');
            $case1 = new TestCase('User module');
            $case1->test('GET / returns 200 OK', function ()use (&$client) {
                $status = null;
                $header = new \lib\http\HttpHeader([
                    \lib\http\HttpHeader::ACCEPT_VERSION => 'api',
                    \lib\http\HttpHeader::ACCEPT => 'application/json'
                ]);
                $client->setHeader($header);
                $client->get('/', function (\lib\http\ResponseEntity $response) use (&$status) {
                    $status = $response->header();
                });
                return $status->getOne(\lib\http\HttpHeader::CONTENT_LENGTH) === '193';
            });
            $case1->test('User can logout', function () {
                return false;
            });
            $case1->test('Delete user', function () {
                return false;
            });
            $case1->test('Add user', function () {
                return true;
            });
            $case1->test('Update user', function () {
                return true;
            });
            $case2 = new TestCase('Sales module');
            $case2->test('Make a sale', function () {
                return false;
            });
            $case2->test('Make an order', function () {
                return true;
            });
            $result->addCase($case1, $case2);
            $a = \test\ResultAnalysis::analyze('/home/coder/Desktop');
            print_r(json_encode($a));
            return $result;
        }
    }

}
