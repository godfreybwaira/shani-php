<?php

/**
 * InputGroup is a user interface component that enhances an input field by adding
 * elements such as text, buttons, or button groups on either side of the input.
 * This is particularly useful for adding context or specific functionality to form inputs.
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;
    use gui\v1\Style;

    final class InputGroup extends Component
    {

        public const TYPE_1 = 0, TYPE_2 = 1;
        private const INPUT_GROUP = 0, INPUT_TYPES = 1, INPUT_MASK = 2;
        private const PROPS = [
            self::INPUT_GROUP => '',
            self::INPUT_MASK => '',
            self::INPUT_TYPES => [self::TYPE_1 => '', self::TYPE_2 => '']
        ];

        /**
         * Create input group elements
         * @param int $groupType Group type value set using InputGroup::TYPE_*
         */
        public function __construct(int $groupType = null)
        {
            parent::__construct('div', self::PROPS);
            $this->addStyle(self::INPUT_GROUP);
            $this->setType($groupType ?? self::TYPE_1);
        }

        /**
         * Set input group type
         * @param int $groupType Values from InputGroup::TYPE_*
         * @return self
         */
        public function setType(int $groupType): self
        {
            $this->addStyle(self::INPUT_TYPES, $groupType);
            return $this;
        }

        public function setMask(string $text): self
        {
            $mask = new Component('span', self::PROPS);
            $mask->addStyle(self::INPUT_MASK)->setContent($text);
            $this->setPadding(Style::SIZE_DEFAULT);
            $this->appendChildren($mask);
            return $this;
        }

        /**
         * Create a form input element
         * @param string $name input unique name
         * @param string $type input type
         * @param string|null $id input unique
         * @return self
         */
        public function setInput(string $name, string $type, ?string $id = null): self
        {
            $tag = ($type === 'select' || $type === 'textarea') ? $type : 'input';
            $input = new Component($tag);
            if ($tag === 'input') {
                $input->setAttribute('type', $type);
            }
            $input->setAttribute('name', $name);
            $input->setAttribute('id', $id ?? $name)->setPadding(Style::SIZE_DEFAULT);
            $this->appendChildren($input);
            return $this;
        }

        /**
         * Create label for input
         * @param string $text Label texts
         * @param string|null $refId input id that a label references to
         * @return self
         */
        public function setLabel(string $text, ?string $refId = null): self
        {
            $label = new Component('label');
            if ($refId !== null) {
                $label->setAttribute('for', $refId);
            }
            $label->setContent($text)->setPadding(Style::SIZE_DEFAULT);
            return $this->appendChildren($label);
        }
    }

}
