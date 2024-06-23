<?php

/**
 * Description of Event
 * @author coder
 *
 * Created on: Mar 5, 2024 at 10:22:08 AM
 */

namespace shani\server\swoole {

    class Event implements \shani\contracts\Event
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
