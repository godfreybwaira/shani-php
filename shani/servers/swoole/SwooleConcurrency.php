<?php

/**
 * Description of SwooleConcurrency
 * @author coder
 *
 * Created on: Apr 5, 2024 at 11:37:53 PM
 */

namespace shani\servers\swoole {

    use shani\contracts\ConcurrencyInterface;

    final class SwooleConcurrency implements ConcurrencyInterface
    {

        public function async(\Closure $callback): void
        {
            \Swoole\Event::defer($callback);
        }

        public function parallel(\Closure $callback): void
        {
            \Swoole\Coroutine\go($callback);
        }

        public function sleep(int $seconds): void
        {
            \Swoole\Coroutine::sleep($seconds);
        }
    }

}
