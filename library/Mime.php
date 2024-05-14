<?php

/**
 * Description of Mime
 * @author coder
 *
 * Created on: Mar 30, 2024 at 10:03:10 AM
 */

namespace library {

    final class Mime implements \shani\adaptor\Handler
    {

        private static \shani\adaptor\Cacheable $mime;

        /**
         * Determines the mimetype of a file by looking at its extension.
         *
         */
        public static function fromFilename(string $filename): ?string
        {
            return self::fromExtension(pathinfo($filename, PATHINFO_EXTENSION));
        }

        /**
         * Maps a file extensions to a mimetype.
         *
         */
        public static function fromExtension(string $extension): ?string
        {
            $ext = strtolower($extension);
            $type = self::$mime->get($ext);
            if (!$type) {
                $type = \shani\Config::mime($ext);
                self::$mime->replace($ext, $type);
            }
            return $type;
        }

        public static function parse(?string $mimeStr): ?array
        {
            if ($mimeStr !== null) {
                return array_map(fn($val) => explode(';', trim($val))[0], explode(',', strtolower($mimeStr)));
            }
            return null;
        }

        public static function explode(?string $mimeStr): ?array
        {
            if ($mimeStr === null) {
                return null;
            }
            $mime = self::parse($mimeStr)[0];
            return explode('/', $mime);
        }

        public static function setHandler($handler): void
        {
            static::$mime = $handler;
        }
    }

}

