<?php

/**
 * Description of Logger
 * @author coder
 *
 * Created on: Apr 7, 2025 at 3:14:22 PM
 */

namespace shani\core\log {

    use lib\ds\map\ReadableMap;

    final class Logger
    {

        private $handler = null;
        private ?\SplFileObject $stream = null;

        public function __construct(?string $filepath = null)
        {
            if ($filepath !== null) {
                $this->stream = new \SplFileObject($filepath, 'a+b');
            }
        }

        public function __destruct()
        {
            if ($this->stream !== null) {
                unset($this->stream);
            }
        }

        private function writeMessage(string $message, LogLevel $level): self
        {
            $structure = new LogStructure($message, $level);
            if ($this->stream !== null) {
                $this->stream->fwrite($structure);
                return $this;
            }
            return $this->writeConsoleMessage($structure, $level);
        }

        private function writeConsoleMessage(LogStructure $structure, LogLevel $level): self
        {
            $space = str_repeat(' ', 3);
            $textColor = match ($level) {
                LogLevel::EMERGENCY => ConsolePrinter::COLOR_MAGENTA,
                LogLevel::WARNING => ConsolePrinter::COLOR_YELLOW,
                LogLevel::ERROR => ConsolePrinter::COLOR_RED,
                LogLevel::INFO => ConsolePrinter::COLOR_CYAN,
            };
            $text = $structure->time . $space;
            $text .= '[ ' . ConsolePrinter::colorText($structure->level, $textColor) . ' ]';
            if (PHP_SAPI === 'cli') {
                echo $text . $space . $structure->message . PHP_EOL;
            }
            return $this;
        }

        public function warning(string $message): self
        {
            return $this->log(LogLevel::WARNING, $message);
        }

        public function error(string $message): self
        {
            return $this->log(LogLevel::ERROR, $message);
        }

        public function emergency(string $message): self
        {
            return $this->log(LogLevel::EMERGENCY, $message);
        }

        public function info(string $message): self
        {
            return $this->log(LogLevel::INFO, $message);
        }

        /**
         * Log exceptions to a log file/destination
         * @param LogLevel $level Log level
         * @param string $message Message to log
         * @return self
         */
        public function log(LogLevel $level, string $message): self
        {
            if (empty($this->handler)) {
                return $this->writeMessage($message, $level);
            }
            $structure = new LogStructure($message, $level);
            $cb = $this->handler;
            $data = $cb($structure);
            if ($this->stream !== null && ($data !== null || $data !== '')) {
                $this->stream->fwrite($data);
            }
            return $this;
        }

        /**
         * Set a logger handler for handling logs
         * @param callable $cb A callback for handling log i.e <code>$cb(LogStructure $s):?string</code>
         * @return self
         */
        public function setHandler(callable $cb): self
        {
            $this->handler = $cb;
            return $this;
        }
    }

}
