<?php

/**
 * Description of TestSeverity
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:17:55 PM
 */

namespace test\helpers {

    enum TestSeverity
    {

        /**
         * Test case is of low importance
         */
        case LOW;

        /**
         * Test case is of medium importance
         */
        case MEDIUM;

        /**
         * Test case is of high importance
         */
        case HIGH;
    }

}
