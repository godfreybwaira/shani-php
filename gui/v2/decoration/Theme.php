<?php

/**
 * Description of Theme
 * @author coder
 *
 * Created on: Mar 25, 2025 at 12:07:49 PM
 */

namespace gui\v2\decoration {

    abstract class Theme
    {

        private readonly string $themeName, $themeId;
        private static string $themeColor = '#041e49'; //dark blue

        /**
         * Create a theme with unique name
         * @param string $themeName Theme name
         */
        protected function __construct(string $themeName)
        {
            $this->themeName = $themeName;
            $this->themeId = self::createId();
        }

        /**
         * Create unique ID
         * @return string
         */
        public static function createId(): string
        {
            return 'd' . substr(hrtime(true), 8);
        }

        /**
         * Change theme color
         * @param string $color Hexadecimal color e.g #0000ff
         * @return void
         */
        public static function changeColor(string $color): void
        {
            self::$themeColor = $color;
        }

        public static function getColor(): string
        {
            return self::$themeColor;
        }

        /**
         * Get theme unique name
         * @return string
         */
        public final function getName(): string
        {
            return $this->themeName;
        }

        /**
         * Get CSS class
         * @return string
         */
        public final function getClass(): string
        {
            return $this->themeId;
        }

        /**
         * Get Component decoration (styles)
         */
        public abstract function getDecoration(): ?string;
    }

}
