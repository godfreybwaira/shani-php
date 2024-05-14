<?php

/**
 * Description of Concurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:37:53 PM
 */

namespace shani\server\swoole {

    final class Concurrency implements \shani\adaptor\Concurrency
    {

        public function async(callable $callback): void
        {
            \Swoole\Event::defer($callback);
        }

        public function thread(callable $callback): void
        {
            \Swoole\Coroutine\go($callback);
        }

        public function sleep(int $seconds): void
        {
            \Swoole\Coroutine::sleep($seconds);
        }
    }

}
