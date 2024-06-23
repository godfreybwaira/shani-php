<?php

/**
 * Description of Request
 * @author coder
 *
 * Created on: Mar 25, 2024 at 12:36:29 PM
 */

namespace shani\contracts {

    interface Request
    {

        public function method(): string;

        public function protocol(): string;

        public function raw(): ?string;

        public function ip(): string;

        public function time(): int;

        public function cookies(): ?array;

        public function post(): ?array;

        public function get(): ?array;

        public function headers(): ?array;

        public function files(): ?array;

        public function uri(): \library\URI;
    }

}
