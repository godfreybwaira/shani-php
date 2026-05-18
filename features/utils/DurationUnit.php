<?php

/**
 * Description of DurationUnit
 * @author coder
 *
 * Created on: Mar 12, 2025 at 11:32:07 AM
 */

namespace features\utils {

    enum DurationUnit
    {

        case SECONDS;
        case MINUTES;
        case HOURS;
        case DAYS;
        case WEEKS;
        case MONTHS;
        case YEARS;
    }

}
