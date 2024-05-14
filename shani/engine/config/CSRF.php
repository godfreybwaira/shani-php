<?php

/**
 * Description of CSRF
 * @author coder
 *
 * Created on: Feb 16, 2024 at 12:02:36 AM
 */

namespace shani\engine\config {

    interface CSRF {

        public const PROTECTION_OFF = 0;
        public const PROTECTION_STRICT = 1;
        public const PROTECTION_FLEXIBLE = 2;
    }

}
