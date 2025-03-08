<?php

/**
 * Description of UserAppDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\documentation\dto {

    final class UserAppDto
    {

        private string $appName;
        private array $modules = [];

        public function __construct(string $appName)
        {
            $this->appName = $appName;
        }

        public function addModule(ModuleDto $module): self
        {
            $this->modules[] = $module->dto();
            return $this;
        }

        public function dto(): array
        {
            return [
                'name' => $this->appName,
                'modules' => $this->modules
            ];
        }
    }

}
