<?php

/**
 * Description of ControllerDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\documentation\dto {

    final class ControllerDto
    {

        private readonly string $name;
        private array $methods = [];

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        public function addRequestMethod(RequestMethodDto $method): self
        {
            $this->methods[] = $method->dto();
            return $this;
        }

        public function dto(): array
        {
            return [
                'name' => $this->name,
                'methods' => $this->methods
            ];
        }
    }

}
