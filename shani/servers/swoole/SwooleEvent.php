<?php

/**
 * Description of SwooleEvent
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace shani\servers\swoole {

    use shani\contracts\EventHandler;

    class SwooleEvent implements EventHandler
    {

        public function dispatch(array $callbacks, callable $finish, ...$params): self
        {
            $size = count($callbacks);
            $ch = new \Swoole\Coroutine\Channel($size);
            foreach ($callbacks as $cb) {
                \Swoole\Coroutine\go(fn() => $ch->push($cb(...$params)));
            }
            \Swoole\Coroutine\go(function () use (&$ch, &$size, &$finish) {
                $data = [];
                for ($length = 0; $length < $size; $length++) {
                    $data[] = $ch->pop();
                }
                $finish($data);
            });
            return $this;
        }
    }

}
