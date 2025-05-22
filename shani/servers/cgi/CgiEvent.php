<?php

/**
 * Description of CgiEvent
 * @author coder
 *
 * Created on: May 22, 2025 at 11:40:48 AM
 */

namespace shani\servers\cgi {

    use shani\contracts\EventHandler;

    final class CgiEvent implements EventHandler
    {

        public function dispatch(array $callbacks, callable $finish, ...$params): self
        {
            $data = [];
            foreach ($callbacks as $cb) {
                $data[] = $cb(...$params);
            }
            $finish($data);
            return $this;
        }
    }

}
