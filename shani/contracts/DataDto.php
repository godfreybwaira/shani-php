<?php

/**
 * DataDto
 * @author coder
 *
 * Created on: Feb 25, 2025 at 7:44:35 PM
 */

namespace shani\contracts {

    interface DataDto
    {

        /**
         * Converts DTO object to an associated array
         * @return array|null
         */
        public function asMap(): ?array;
    }

}
