<?php

/**
 * Description of Style
 * @author coder
 *
 * Created on: Jul 14, 2024 at 1:36:13 PM
 */

namespace gui\v1 {

    use gui\v1\decoration\Shadow;
    use lib\Map;
    use UI\Size;

    final class Style
    {

        ///////////////////////
        ///////////////////////
        public const POS_TL = 0, POS_TC = 1, POS_TR = 2, POS_CL = 3, POS_CC = 4, POS_CR = 5, POS_BL = 6;
        public const POS_BC = 7, POS_BR = 8, POS_TOP = 9, POS_LEFT = 10, POS_BOTTOM = 11, POS_RIGHT = 12;
        ///////////////////////
        public const ALIGN_CENTER = 0, ALIGN_START = 1, ALIGN_END = 2, ALIGN_STRETCH = 3;
        public const ALIGN_VERTICAL = 4, ALIGN_HORIZONTAL = 5;
        public const GAP_X = 0, GAP_Y = 1, GAP_XY = 2;
        ///////////////////////
        ///////////////////////
        public const SHADOW_XY = 0, SHADOW_TR = 1, SHADOW_TL = 2, SHADOW_BL = 3, SHADOW_BR = 4;
        public const SHADOW_DEFAULT = Shadow::BR;
        ///////////////////////

        private const PROPS = [
            'colors' => [
                self::COLOR_DANGER => 'crimson', self::COLOR_SUCCESS => '', self::COLOR_ALERT => 'goldenrod',
                self::COLOR_INFO => '', self::COLOR_PRIMARY => '', self::COLOR_SECONDARY => '',
                self::COLOR_TRANSLUSCENT => ''
            ],
            'positions' => [
                self::POS_BC => '', self::POS_BL => '', self::POS_BOTTOM => '',
                self::POS_BR => '', self::POS_CC => '', self::POS_CL => '',
                self::POS_CR => '', self::POS_LEFT => '', self::POS_RIGHT => '',
                self::POS_TC => '', self::POS_TL => '', self::POS_TOP => '',
                self::POS_TR => ''
            ],
            'alignments' => [
                self::ALIGN_CENTER => '', self::ALIGN_END => '',
                self::ALIGN_START => '', self::ALIGN_STRETCH => ''
            ],
            'gaps' => [
                self::GAP_X => '', self::GAP_Y => '', self::GAP_XY => ''
            ],
            'gap_sizes' => [
                Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'margin_sizes' => [
                Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'padding_sizes' => [
                Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'font_sizes' => [
                Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'shadow_sizes' => [
                Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'corner_sizes' => [
                Size::FULL => '', Size::LG => '', Size::MD => '',
                Size::SM => '', Size::XL => ''
            ],
            'corner_radius' => [
                self::POS_BL => '', self::POS_BOTTOM => '',
                self::POS_BR => '', self::POS_LEFT => '',
                self::POS_RIGHT => '', self::POS_TL => '',
                self::POS_TOP => '', self::POS_TR => ''
            ],
            'shadow_directions' => [
                Shadow::BOTTOM_LEFT => '', Shadow::BOTTOM_RIGHT => '', Shadow::TOP_LEFT => '',
                Shadow::TOP_RIGHT => '', Shadow::XY => ''
            ],
            'border' => '', 'active' => '', 'relative_position' => '',
            'full_width' => '', 'full_height' => ''
        ];

        public static function getStyle(string $key): array
        {
            return self::PROPS[$key] ?? [];
        }

        public static function getStyles(array $keys): array
        {
            $styles = [];
            foreach ($keys as $key) {
                $styles[$key] = self::PROPS[$key] ?? null;
            }
            return $styles;
        }
    }

}
