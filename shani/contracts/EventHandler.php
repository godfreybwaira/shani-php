<?php

/**
 * Description of EventHandler
 * @author coder
 *
 * Created on: Mar 25, 2024 at 7:34:27 PM
 */

namespace shani\contracts {

    interface EventHandler
    {

        public function dispatch(array $callbacks, callable $finish, ...$params): self;
    }

}
