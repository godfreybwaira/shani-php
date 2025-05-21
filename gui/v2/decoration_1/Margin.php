<?php

/**
 * Description of Margin
 * @author coder
 *
 * Created on: Apr 17, 2025 at 3:42:32 PM
 */

namespace gui\v2\decoration {

    final class Margin extends Spacing
    {

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            parent::__construct('margin', $unit);
        }
    }

}
