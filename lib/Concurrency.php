<?php

/**
 * Description of Concurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:37:53 PM
 */

namespace lib {

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

        /**
         * Call a callback function asynchronously which does not block the execution
         * of program
         * @param callable $callback A callback function to execute
         * @return void
         */
        public static function async(callable $callback): void
        {
            if (!isset(self::$obj)) {
                $callback();
                return;
            }
            self::$obj->async($callback);
        }

        /**
         * Call a callback function in a separate lightweight thread.
         * @param callable $callback A callback function to execute
         * @return void
         */
        public static function thread(callable $callback): void
        {
            if (!isset(self::$obj)) {
                $callback();
                return;
            }
            self::$obj->thread($callback);
        }

        /**
         * Cause a program to sleep at a given interval before continuing
         * execution.
         * @param int $seconds Number of seconds to sleep
         * @return void
         */
        public static function sleep(int $seconds): void
        {
            if ($seconds > 0) {
                if (!isset(self::$obj)) {
                    sleep($seconds);
                    return;
                }
                self::$obj->sleep($seconds);
            }
        }
    }

}
