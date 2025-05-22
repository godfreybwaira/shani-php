<?php

/**
 * Description of CgiConcurrency
 * @author coder
 *
 * Created on: May 22, 2025 at 11:41:30 AM
 */

namespace shani\servers\cgi {

    use shani\contracts\ConcurrencyInterface;

    final class CgiConcurrency implements ConcurrencyInterface
    {

        public function async(callable $callback): void
        {
            $callback();
        }

        public function parallel(callable $callback): void
        {
            $callback();
        }

        public function sleep(int $seconds): void
        {
            sleep($seconds);
        }
    }

}
