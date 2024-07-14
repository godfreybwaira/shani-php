<?php

/**
 * ToggleInput is a user interface element that allows users to switch between
 * two states, such as on/off or true/false.
 * @author coder
 *
 * Created on: May 13, 2024 at 3:23:35 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ToggleInput extends Component
    {

        public const TYPE_1 = 0, TYPE_2 = 1;
        private const TOGGLE = 0, TOGGLE_TYPES = 1;
        private const PROPS = [
            self::TOGGLE => '',
            self::TOGGLE_TYPES => [self::TYPE_1 => '', self::TYPE_2 => '']
        ];

        public function __construct(string $name)
        {
            parent::__construct('input', self::PROPS);
            $this->setAttribute('type', 'checkbox');
            $this->addProperty(self::TOGGLE)->setAttribute('name', $name);
        }

        /**
         * Set input type
         * @param int $toggleType Input type from ToggleInput::TYPE_*
         * @return self
         */
        public function setType(int $toggleType): self
        {
            return $this->addProperty(self::TOGGLE_TYPES, $toggleType);
        }
    }

}
