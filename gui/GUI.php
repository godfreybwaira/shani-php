<?php

/**
 * Description of GUI
 * @author coder
 *
 * Created on: Mar 14, 2024 at 9:24:08 AM
 */

namespace gui {

    interface GUI
    {

        public function render(mixed $data, mixed $state): void;
    }

}
