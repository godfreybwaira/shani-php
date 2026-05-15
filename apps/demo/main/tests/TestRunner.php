<?php

namespace apps\demo\main\tests {

    use features\test\TestResult;
    use features\test\TestRunnerInterface;
    use shani\launcher\Framework;

    final class TestRunner implements TestRunnerInterface
    {

        #[\Override]
        public function runTest(): TestResult
        {
            $result = new TestResult('UAT for my application', location: Framework::DIR_SERVER_STORAGE);
            //TODO
            return $result;
        }
    }

}

