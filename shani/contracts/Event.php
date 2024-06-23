<?php

/**
 * Description of Event
 * @author coder
 *
 * Created on: Mar 25, 2024 at 7:34:27 PM
 */

namespace shani\contracts {

    interface Event
    {

        public function dispatch(array $callbacks, callable $finish, ...$params): self;
    }

}
