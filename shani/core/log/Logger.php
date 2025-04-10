<?php

/**
 * Description of Logger
 * @author coder
 *
 * Created on: Apr 7, 2025 at 3:14:22 PM
 */

namespace shani\core\log {

    use lib\map\ReadableMap;

    final class Logger
    {

        private array $handlers = [];
        private \SplFileObject $stream;

        public function __construct(string $filepath)
        {
            $this->stream = new \SplFileObject($filepath, 'a+b');
        }

        private function createMessage(string $message, LogLevel $level, ?array $context = null, ?string $extra = null): string
        {
            $structure = new LogStructure($message, $level, $context, $extra);
            $text = $structure->time . "\t" . $structure->level . "\t";
            $text .= $structure->message . "\t" . $structure->context . "\t";
            $text .= $structure->extra . PHP_EOL;
            try {
                if ($this->stream->getSize() === 0) {
                    $header = "time\tlevel\tmessage\tcontext\textra" . PHP_EOL;
                    return $header . $text;
                }
            } catch (\Throwable $t) {
                $text = $this->createConsoleMessage($structure, $level);
            }
            return $text;
        }

        private function createConsoleMessage(LogStructure $structure, LogLevel $level): string
        {
            $space = str_repeat(' ', 3);
            list($textColor, $bgColor) = match ($level) {
                LogLevel::EMERGENCY => [ConsolePrinter::COLOR_BLACK, ConsolePrinter::COLOR_MAGENTA],
                LogLevel::WARNING => [ConsolePrinter::COLOR_BLACK, ConsolePrinter::COLOR_YELLOW],
                LogLevel::ERROR => [ConsolePrinter::COLOR_BLACK, ConsolePrinter::COLOR_RED],
                LogLevel::INFO => [ConsolePrinter::COLOR_BLACK, ConsolePrinter::COLOR_BLUE],
            };
            $text = $structure->time . $space;
            $text .= ConsolePrinter::colorText(' ' . $structure->level . ' ', $textColor, $bgColor);
            $text .= $space . $structure->message . $space . $structure->context . $space;
            return $text . $structure->extra . PHP_EOL;
        }

        public function warning(string $message, ?array $context = null): self
        {
            $text = $this->createMessage($message, LogLevel::WARNING, $context);
            $this->stream->fwrite($text);
            return $this;
        }

        public function error(string $message, ?array $context = null): self
        {
            $text = $this->createMessage($message, LogLevel::ERROR, $context);
            $this->stream->fwrite($text);
            return $this;
        }

        public function emergency(string $message, ?array $context = null): self
        {
            $text = $this->createMessage($message, LogLevel::EMERGENCY, $context);
            $this->stream->fwrite($text);
            return $this;
        }

        public function info(string $message, ?array $context = null): self
        {
            $text = $this->createMessage($message, LogLevel::INFO, $context);
            $this->stream->fwrite($text);
            return $this;
        }

        public function log(\Throwable $t, LogLevel $level, ?array $context = null): self
        {
            $extra = 'File: ' . $t->getFile() . ':' . $t->getLine();
            $structure = new LogStructure($t->getMessage(), $level, $context, $extra);
            $text = $this->createMessage($t->getMessage(), $level, $context, $extra);
            $this->stream->fwrite($text);
            foreach ($this->handlers as $levelName => $cb) {
                if ($levelName === $level->name) {
                    $cb($structure);
                }
            }
            return $this;
        }

        public function addHandler(LogLevel $level, callable $cb): self
        {
            $this->handlers[$level->name] = $cb;
            return $this;
        }

        public function read(): ?ReadableMap
        {
            return new ReadableMap($this->stream->fgetcsv("\t"));
        }
    }

}
