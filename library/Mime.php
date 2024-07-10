<?php

/**
 * Description of Mime
 * @author coder
 *
 * Created on: Mar 30, 2024 at 10:03:10 AM
 */

namespace library {

    final class Mime implements \shani\contracts\Handler
    {

        private static \shani\contracts\Cacheable $mime;

        /**
         * Determines the mime type of a file by looking at its extension.
         *
         */
        public static function fromFilename(string $filename): ?string
        {
            return self::fromExtension(pathinfo($filename, PATHINFO_EXTENSION));
        }

        /**
         * Maps a file extensions to a mime type.
         *
         */
        public static function fromExtension(string $extension): ?string
        {
            $ext = strtolower($extension);
            $type = self::$mime->get($ext);
            if (!$type) {
                $type = \shani\ServerConfig::mime($ext);
                self::$mime->replace($ext, $type);
            }
            return $type;
        }

        /**
         * Parse MIME types and return array of content types and their character sets
         * @param string|null $mimeStr A valid mime string separated by comma
         * @return array|null
         */
        public static function parse(?string $mimeStr): ?array
        {
            if ($mimeStr !== null) {
                return array_map(fn($val) => explode(';', trim($val))[0], explode(',', strtolower($mimeStr)));
            }
            return null;
        }

        /**
         * Explode a mime string and return array of two values where first value
         * is the generic value and the second value is specific value
         * @param string|null $mimeStr A valid mime string
         * @return array|null
         */
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

