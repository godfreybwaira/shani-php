<?php

/**
 * Description of LogStructure
 * @author coder
 *
 * Created on: Apr 7, 2025 at 4:40:54 PM
 */

namespace shani\core\log {

    final class LogStructure implements \JsonSerializable
    {

        public readonly ?string $context, $extra;
        public readonly string $message, $level, $time;

        public function __construct(string $message, LogLevel $level, ?array $context, ?string $extra)
        {
            $this->extra = $extra;
            $this->message = $message;
            $this->level = $level->name;
            $this->time = date(DATE_ATOM);
            $this->context = json_encode($context);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'time' => $this->time,
                'message' => $this->message,
                'level' => $this->level,
                'context' => $this->context,
                'extra' => $this->extra
            ];
        }
    }

}
