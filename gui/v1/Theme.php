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

        public const DEFAULT_SIZE = Component::SIZE_MD;

        private static array $styles;

        public static function setStyles(array $styles)
        {
            self::$styles = $styles;
        }

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
