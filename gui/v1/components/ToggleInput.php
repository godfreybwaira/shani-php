<?php

/**
 * Description of ToggleInput
 * @author coder
 *
 * Created on: May 13, 2024 at 3:23:35 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ToggleInput extends Component
    {

        private const NAME = 'toggle', TYPES = ['type-1', 'type-2'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        private string $name;

        public function __construct(string $name)
        {
            parent::__construct('input');
            $this->setAttribute('type', 'checkbox');
            $this->name = $name;
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function build(): string
        {
            if ($this->type !== null) {
                $this->addProperty(self::NAME, $this->type);
            }
            return parent::build();
        }
    }

}
