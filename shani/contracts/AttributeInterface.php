<?php

/**
 * Description of AttributeInterface
 * @author goddy
 *
 * @since May 18, 2026 at 9:31:23 AM
 */

namespace shani\contracts {

    use shani\launcher\App;

    interface AttributeInterface
    {

        public function execute(App $app): void;
    }

}
