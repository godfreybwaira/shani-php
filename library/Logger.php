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
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/alerts', $text, '/alert-');
            return $this;
        }

        public function error(string $text): self
        {
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/user-errors', $text, '/user-error-');
            return $this;
        }

        public function emergency(string $text): self
        {
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/emergencies', $text, '/emergency-');
            return $this;
        }

        public function warning(string $text): self
        {
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/warnings', $text, '/warning-');
            return $this;
        }

        public function info(string $text): self
        {
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/infos', $text, '/info-');
            return $this;
        }

        public function debug(string $text): self
        {
            $text = 'Time: ' . date('H:i:s') . PHP_EOL . $text . PHP_EOL;
            self::writer($this->destination . '/debugs', $text, '/debug-');
            return $this;
        }

        public function appError(int $errno, string $errstr, string $errfile, int $errline): self
        {
            $content = 'Time: ' . date('H:i:s') . PHP_EOL;
            $content .= 'Code: ' . $errno . PHP_EOL;
            $content .= 'Message: ' . $errstr . PHP_EOL;
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
