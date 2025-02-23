<?php

/**
 * Description of UserAppDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\engine\documentation\dto {

    final class UserAppDto
    {

        private string $appName, $version;
        private array $modules = [];

        public function __construct(string $appName, string $version)
        {
            $this->appName = $appName;
            $this->version = $version;
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
                'version' => $this->version,
                'modules' => $this->modules
            ];
        }
    }

}
