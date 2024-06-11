<?php

/**
 * Description of Concurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:37:53 PM
 */

namespace library {

    use shani\contracts\Concurrency as ConcurrencyInterface;

    final class Concurrency
    {

        private static ConcurrencyInterface $obj;

        public function __construct(ConcurrencyInterface $obj)
        {
            if (!isset(self::$obj)) {
                self::$obj = $obj;
            }
        }

        public static function async(callable $callback): void
        {
            self::$obj->async($callback);
        }

        public static function sleep(int $seconds): void
        {
            if ($seconds > 0) {
                self::$obj->sleep($seconds);
            }
        }
    }

}
