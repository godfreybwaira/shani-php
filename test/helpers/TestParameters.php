<?php

/**
 * Description of TestParameter
 * @author coder
 *
 * Created on: Jun 26, 2025 at 1:58:54 PM
 */

namespace test\helpers {

    final class TestParameters
    {

        public readonly string $host, $profile;

        /**
         * Create test parameters
         * @param string $host HTTP host name
         * @param string $profile Test profile in which this test is done under. See host yaml file
         */
        public function __construct(string $host, string $profile)
        {
            $this->host = $host;
            $this->profile = $profile;
        }
    }

}
