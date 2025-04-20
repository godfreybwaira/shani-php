<?php

/**
 * Description of DimUnit
 * @author coder
 *
 * Created on: Apr 17, 2025 at 2:31:13 PM
 */

namespace gui\v2\decoration {

    enum DimUnit: string
    {

        case EM = 'em';
        case PERCENT = '%';
        case PIXEL = 'px';

        //2,1,.5,.25=em
        //32,16,8,4=px
        public static function small(self $unit): float
        {
            return match ($unit) {
                self::EM => .25,
                self::PX => 4.0,
                default => .25
            };
        }

        public static function medium(self $unit): float
        {
            return match ($unit) {
                self::EM => .5,
                self::PX => 8.0,
                default => .5
            };
        }

        public static function large(self $unit): float
        {
            return match ($unit) {
                self::EM => 1.0,
                self::PX => 16.0,
                default => 1.0
            };
        }

        public static function xlarge(self $unit): float
        {
            return match ($unit) {
                self::EM => 2.0,
                self::PX => 32.0,
                default => 2.0
            };
        }
    }

}
