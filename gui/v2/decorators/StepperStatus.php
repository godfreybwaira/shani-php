<?php

/**
 * Description of StepperStatus
 * @author coder
 *
 * Created on: Jun 5, 2025 at 1:17:03 PM
 */

namespace gui\v2\decorators {

    enum StepperStatus: string
    {

        case ACTIVE = 'active';
        case COMPLETE = 'complete';
    }

}
