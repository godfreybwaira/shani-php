<?php

/**
 * Description of Style
 * @author coder
 *
 * Created on: Jul 14, 2024 at 1:36:13 PM
 */

namespace gui\v1 {

    final class Style
    {

        public const ANINATION_SLIDE_LEFT = 0, ANINATION_SLIDE_RIGHT = 1;
        public const ANINATION_SLIDE_TOP = 2, ANINATION_SLIDE_BOTTOM = 3;
        public const ANINATION_FADE = 4;
        ///////////////////////
        public const COLOR_DANGER = 0, COLOR_SUCCESS = 1, COLOR_ALERT = 2, COLOR_INFO = 3;
        public const COLOR_PRIMARY = 4, COLOR_SECONDARY = 5, COLOR_TRANSLUSCENT = 6;
        ///////////////////////
        public const POS_TL = 0, POS_TC = 1, POS_TR = 2, POS_CL = 3, POS_CC = 4, POS_CR = 5, POS_BL = 6;
        public const POS_BC = 7, POS_BR = 8, POS_TOP = 9, POS_LEFT = 10, POS_BOTTOM = 11, POS_RIGHT = 12;
        ///////////////////////
        public const ALIGN_CENTER = 0, ALIGN_START = 1, ALIGN_END = 2, ALIGN_STRETCH = 3;
        public const ALIGN_VERTICAL = 4, ALIGN_HORIZONTAL = 5;
        public const GAP_X = 0, GAP_Y = 1, GAP_XY = 2;
        ///////////////////////
        public const SIZE_SM = 0, SIZE_MD = 1, SIZE_LG = 2, SIZE_XL = 3, SIZE_FULL = 4;
        public const SIZE_DEFAULT = self::SIZE_MD;
        ///////////////////////
        public const SHADOW_XY = 0, SHADOW_TR = 1, SHADOW_TL = 2, SHADOW_BL = 3, SHADOW_BR = 4;
        public const SHADOW_DEFAULT = self::SHADOW_BR;
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
                self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'margin_sizes' => [
                self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'padding_sizes' => [
                self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'font_sizes' => [
                self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'shadow_sizes' => [
                self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'corner_sizes' => [
                self::SIZE_FULL => '', self::SIZE_LG => '', self::SIZE_MD => '',
                self::SIZE_SM => '', self::SIZE_XL => ''
            ],
            'corner_radius' => [
                self::POS_BL => '', self::POS_BOTTOM => '',
                self::POS_BR => '', self::POS_LEFT => '',
                self::POS_RIGHT => '', self::POS_TL => '',
                self::POS_TOP => '', self::POS_TR => ''
            ],
            'shadow_directions' => [
                self::SHADOW_BL => '', self::SHADOW_BR => '', self::SHADOW_TL => '',
                self::SHADOW_TR => '', self::SHADOW_XY => ''
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
            return \lib\Map::get(self::PROPS, $keys);
        }
    }

}
