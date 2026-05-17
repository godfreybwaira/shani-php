<?php

namespace apps\demo\v1\tests {

    use features\test\helpers\TestCategory;
    use features\test\helpers\TestSeverity;
    use features\test\TestCase;
    use features\test\TestGroup;
    use features\test\TestResult;
    use features\test\TestRunnerInterface;
    use features\utils\HttpClient;
    use features\utils\URI;
    use shani\http\enums\HttpStatus;
    use shani\http\ResponseEntity;

    final class TestRunner implements TestRunnerInterface
    {

        #[\Override]
        public function runTest(): TestResult
        {
            $result = new TestResult('UAT for my application');
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
                $client = new HttpClient(new URI('https://dev.shani.v2.local'));
                $client->enableAsync(false)->enableSSLVerification(false);
                $code = null;
                $client->get('/', function (ResponseEntity $res)use (&$code) {
                    $code = $res->status();
                });
                return $code === HttpStatus::OK;
            }, maxExecutionTime: 7, iterations: 1);
            $g3->addCase($caseg31, $caseg32, $caseg33);
            $result->addGroup($g1, $g2, $g3);
            return $result;
        }
    }

}

