<?php

namespace apps\shop\v1\tests {

    use features\test\TestResult;
    use features\test\TestRunnerInterface;
    use shani\launcher\Framework;

    final class TestRunner implements TestRunnerInterface
    {

        public function runTest(): TestResult
        {
            $result = new TestResult('UAT for my application', location: Framework::DIR_SERVER_STORAGE);
            //TODO
            $group = new \features\test\TestGroup('Arithmetic');
            $case = new \features\test\TestCase(\features\test\helpers\TestSeverity::HIGH);
            $case->test('Test if 1+1 equals 2', fn() => false);
            $group->addCase($case);
            $result->addGroup($group);
            return $result;
        }
    }

}

