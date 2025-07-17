<?php

/**
 * Description of TestParameter
 * @author coder
 *
 * Created on: Jun 26, 2025 at 1:58:54 PM
 */

namespace test {

    final class TestParameters
    {

        public readonly string $host, $env;

        /**
         * Create test parameters
         * @param string $host HTTP host name
         * @param string $env Test enviroment in which this test is done under. See host yaml file
         */
        public function __construct(string $host, string $env)
        {
            $this->host = $host;
            $this->env = $env;
        }
    }

}
