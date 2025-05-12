<?php

/**
 * Description of LogStructure
 * @author coder
 *
 * Created on: Apr 7, 2025 at 4:40:54 PM
 */

namespace shani\core\log {

    final class LogStructure implements \Stringable
    {

        public readonly string $message, $level, $time;

        public function __construct(string $message, LogLevel $level)
        {
            $this->message = $message;
            $this->level = $level->name;
            $this->time = date(DATE_ATOM);
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->time . ' [ ' . $this->level . ' ] ' . $this->message . PHP_EOL;
        }
    }

}
