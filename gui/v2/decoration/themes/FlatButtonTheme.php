<?php

/**
 * Description of FlatButtonTheme
 * @author coder
 *
 * Created on: Apr 20, 2025 at 7:50:15 PM
 */

namespace gui\v2\decoration\themes {

    use gui\v2\decoration\Theme;

    final class FlatButtonTheme extends Theme
    {

        private readonly string $backgroundColor;
        private static self $defaultTheme;

        public function __construct(string $color)
        {
            parent::__construct('flat-button');
            $this->backgroundColor = $color;
        }

        public static function getDefaultTheme(): self
        {
            if (!isset(self::$defaultTheme)) {
                self::$defaultTheme = new self('red');
            }
            return self::$defaultTheme;
        }

        public function getDecoration(): ?string
        {
            return '.' . $this->getId() . '{background-color:' . $this->backgroundColor . '}';
        }
    }

}
