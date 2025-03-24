<?php

/**
 * Description of ModuleDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\documentation\dto {

    final class ModuleDto
    {

        private string $name;
        private array $controllers = [];

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        public function addController(ControllerDto $controller): self
        {
            $this->controllers[] = $controller->dto();
            return $this;
        }

        public function getName(): string
        {
            return $this->name;
        }

        public function dto(): array
        {
            return [
                'name' => $this->name,
                'controllers' => $this->controllers
            ];
        }
    }

}
