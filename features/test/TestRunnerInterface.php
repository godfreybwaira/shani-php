<?php

/**
 * Description of TestRunnerInterface
 * @author goddy
 *
 * Created on: May 15, 2026 at 3:48:15 PM
 */

namespace features\test {

    interface TestRunnerInterface
    {

        public function runTest(): TestResult;
    }

}
