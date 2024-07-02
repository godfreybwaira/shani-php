<?php

/**
 * Description of Theme
 * @author coder
 *
 * Created on: May 6, 2024 at 9:39:10 AM
 */

namespace gui\v1 {

    final class Theme
    {

        private static array $styles;

        /**
         * Set CSS classes to be used in styling a component.
         * @param array $styles Associative array where key is the name representing
         * a style class(es) or list of classes and values is an array of actual
         * CSS class(es) defined in a CSS file
         * @return void
         */
        public static function setStyles(array $styles): void
        {
            self::$styles = $styles;
        }

        /**
         * Get all CSS style class(es) of a component.
         * @param string $classList
         * @return array CSS class(es) used to style a component
         */
        public static function styles(string ...$classList): array
        {
            $list = [];
            foreach ($classList as $class) {
                $list = array_merge($list, self::$styles[$class]);
            }
            return $list;
        }
    }

}
