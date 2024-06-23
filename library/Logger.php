<?php

/**
 * Description of Logger
 * @author coder
 *
 * Created on: May 21, 2024 at 12:01:27 AM
 */

namespace library {

    final class Logger
    {

        private string $destination;

        public function __construct(string $destination)
        {
            $this->destination = $destination;
        }

        public function alert(string $text): self
        {
            self::writer($this->destination . '/alerts', self::text($text), '/alert-');
            return $this;
        }

        public function error(string $text): self
        {
            self::writer($this->destination . '/user-errors', self::text($text), '/user-error-');
            return $this;
        }

        public function emergency(string $text): self
        {
            self::writer($this->destination . '/emergencies', self::text($text), '/emergency-');
            return $this;
        }

        public function warning(string $text): self
        {
            self::writer($this->destination . '/warnings', self::text($text), '/warning-');
            return $this;
        }

        public function info(string $text): self
        {
            self::writer($this->destination . '/infos', self::text($text), '/info-');
            return $this;
        }

        public function debug(string $text): self
        {
            self::writer($this->destination . '/debugs', self::text($text), '/debug-');
            return $this;
        }

        private static function text(string $text): string
        {
            return 'Time: ' . date('H:i:s O') . PHP_EOL . 'Message: ' . $text . PHP_EOL;
        }

        public function appError(int $errno, string $errstr, string $errfile, int $errline): self
        {
            $content = self::text($errstr);
            $content .= 'Code: ' . $errno . PHP_EOL;
            $content .= 'Source: ' . $errfile . PHP_EOL;
            $content .= 'Line: ' . $errline . PHP_EOL;
            self::writer($this->destination . '/app-errors', $content, '/app-error-');
            echo 'Error has occured, please check logs.' . PHP_EOL;
            return $this;
        }

        public function exception(\Exception &$e): self
        {
            return $this->appError($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        }

        private static function writer(string $destination, string $content, string $prefix): void
        {
            Concurrency::async(function () use (&$destination, &$content, &$prefix) {
                if (is_dir($destination) || mkdir($destination, 0744, true)) {
                    $filename = $destination . $prefix . date('Y-m-d') . '.log';
                    $file = fopen($filename, 'a');
                    fwrite($file, $content . PHP_EOL);
                    fclose($file);
                }
            });
        }
    }

}
