<?php

/**
 * Description of TestParameter
 * @author coder
 *
 * Created on: Jun 26, 2025 at 1:58:54 PM
 */

namespace test {

    final class TestParameter
    {

        public readonly string $host, $env;

        public function __construct(array $params)
        {
            if (isset($params['host'], $params['env'])) {
                $this->host = $params['host'];
                $this->env = $params['env'];
            } else {
                $size = count($params);
                for ($i = 0; $i < $size; $i++) {
                    if ($params[$i] === '--host') {
                        $this->host = $params[++$i];
                    } else if ($params[$i] === '--env') {
                        $this->env = $params[++$i];
                    }
                }
//        "--test" "localhost" "--env" "TEST"
            }
        }
    }

}
