<?php

/**
 * Description of TestPerformanceScore
 * @author goddy
 *
 * Created on: Sep 11, 2025 at 7:00:36 PM
 */

namespace features\test\helpers {

    enum TestPerformanceScore
    {

        /**
         * Best test performance score (100% and above)
         */
        case BEST;

        /**
         * Good test performance score (from 75% to less than 100%)
         */
        case GOOD;

        /**
         * Poor test performance score (from 50% to less than 75%)
         */
        case POOR;

        /**
         * Worse test performance score (from greater than 0% to less than 50%)
         */
        case WORSE;

        /**
         * not applicable test performance score (If score is zero, for no zero score)
         */
        case NOT_APPLICABLE;

        /**
         * Check a score percentage and return a score class
         * @param float $score Score in percent
         * @return TestPerformanceScore total performance score
         */
        public static function check(float $score): TestPerformanceScore
        {
            if ($score >= 100) {
                return TestPerformanceScore::BEST;
            }
            if ($score >= 75) {
                return TestPerformanceScore::GOOD;
            }
            if ($score >= 50) {
                return TestPerformanceScore::POOR;
            }
            if ($score > 0) {
                return TestPerformanceScore::WORSE;
            }
            return TestPerformanceScore::NOT_APPLICABLE;
        }

        /**
         * Calculate the percentage score
         * @param float $numerator Numerator
         * @param float $denominator Denominator
         * @return float Percentage score
         */
        public static function calculate(float $numerator, float $denominator): float
        {
            return round(($numerator / $denominator) * 100, 2);
        }
    }

}
