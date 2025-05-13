<?php

/**
 * Description of SwooleConcurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:37:53 PM
 */

namespace shani\servers\swoole {

    final class SwooleConcurrency implements \shani\contracts\Concurrency
    {

        public function async(callable $callback): void
        {
            \Swoole\Event::defer($callback);
        }

        public function parallel(callable $callback): void
        {
            \Swoole\Coroutine\go($callback);
        }

        public function sleep(int $seconds): void
        {
            \Swoole\Coroutine::sleep($seconds);
        }
    }

}
