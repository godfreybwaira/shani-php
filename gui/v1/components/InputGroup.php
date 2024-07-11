<?php

/**
 * InputGroup is a user interface component that enhances an input field by adding
 * elements such as text, buttons, or button groups on either side of the input.
 * This is particularly useful for adding context or specific functionality to form inputs.
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class InputGroup extends Component
    {

        private const NAME = 'input-group', TYPES = ['1', '2'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        public function __construct()
        {
            parent::__construct('div');
            $this->addProperty(self::NAME);
            $this->setType(self::TYPE_1);
        }

        /**
         * Set input group type
         * @param int $inputType Values from InputGroup::TYPE_*
         * @return self
         */
        public function setType(int $inputType): self
        {
            $this->addProperty('input-type', self::TYPES[$inputType]);
            return $this;
        }

        public function setMask(string $text): self
        {
            $mask = new Component('span', false);
            $mask->addProperty('input-mask')->setContent($text)->setPadding(parent::SIZE_DEFAULT);
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
            $input = new Component($tag, false);
            if ($tag === 'input') {
                $input->setAttribute('type', $type);
            }
            $input->setAttribute('name', $name);
            $input->setAttribute('id', $id ?? $name)->setPadding(parent::SIZE_DEFAULT);
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
            $label = new Component('label', false);
            if ($refId !== null) {
                $label->setAttribute('for', $refId);
            }
            $label->setContent($text)->setPadding(parent::SIZE_DEFAULT);
            $this->appendChildren($label);
            return $this;
        }
    }

}
