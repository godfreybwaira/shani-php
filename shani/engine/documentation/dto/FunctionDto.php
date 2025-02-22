<?php

/**
 * Description of FunctionDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\engine\documentation\dto {

    final class FunctionDto
    {

        private readonly string $name, $target, $endpoint, $details, $targetId;

        public function __construct(string $name, string $target, string $endpoint, string $details)
        {
            $this->name = $name;
            $this->target = $target;
            $this->endpoint = $endpoint;
            $this->details = $details;
            $this->targetId = \shani\engine\http\App::digest($target);
        }

        public function dto(): array
        {
            return [
                'name' => $this->name,
                'target' => $this->target,
                'targetId' => $this->targetId,
                'endpoint' => $this->endpoint,
                'details' => $this->details
            ];
        }
    }

}
