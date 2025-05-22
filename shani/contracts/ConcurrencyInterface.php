<?php

/**
 * Description of ConcurrencyInterface
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:23:22 PM
 */

namespace shani\contracts {

    interface ConcurrencyInterface
    {

        public function async(callable $callback): void;

        public function parallel(callable $callback): void;

        public function sleep(int $seconds): void;
    }

}
