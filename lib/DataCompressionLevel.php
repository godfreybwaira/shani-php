<?php

/**
 * Description of DataCompressionLevel
 * @author coder
 *
 * Created on: Mar 5, 2025 at 10:48:56 AM
 */

namespace lib {

    enum DataCompressionLevel: int
    {

        case DISABLE = 0;
        case LOWEST = 1;
        case LOWER = 2;
        case LOW = 3;
        case BETTER = 4;
        case BEST = 5;
        case GOOD = 6;
        case BAD = 7;
        case WORSE = 8;
        case WORST = 9;
    }

}
