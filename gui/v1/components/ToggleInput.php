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

        private const NAME = 'toggle', TYPES = ['1', '2'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        public function __construct(string $name)
        {
            parent::__construct('input');
            $this->setAttribute('type', 'checkbox')->setAttribute('name', $name);
        }

        /**
         * Set input type
         * @param int $toggleType Input type from ToggleInput::TYPE_*
         * @return self
         */
        public function setType(int $toggleType): self
        {
            return $this->addProperty(self::NAME . '-type', self::TYPES[$toggleType]);
        }
    }

}
