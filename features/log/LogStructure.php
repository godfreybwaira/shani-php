<?php

/**
 * Description of LogStructure
 * @author coder
 *
 * Created on: Apr 7, 2025 at 4:40:54 PM
 */

namespace features\log {

    final class LogStructure implements \Stringable
    {

        public readonly string $message, $level;

        public const NOW = SHANI_CURRENT_TIMESTAMP;

        public function __construct(string $message, LogLevel $level)
        {
            $this->message = $message;
            $this->level = $level->name;
        }

        #[\Override]
        public function __toString(): string
        {
            return self::NOW . ' [ ' . $this->level . ' ] ' . $this->message . PHP_EOL;
        }
    }

}
