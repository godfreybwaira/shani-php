<?php

/**
 * Description of RequestMethodDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\engine\documentation\dto {

    final class RequestMethodDto
    {

        private string $name;
        private array $functions = [];

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        public function addFunction(FunctionDto $function): self
        {
            $this->functions[] = $function->dto();
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
                'functions' => $this->functions
            ];
        }
    }

}
