<?php

/**
 * Description of Concurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:23:22 PM
 */

namespace shani\contracts {

    interface Concurrency
    {

        public function async(callable $callback): void;

        public function parallel(callable $callback): void;

        public function sleep(int $seconds): void;
    }

}
