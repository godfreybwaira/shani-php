<?php

/**
 * Description of ChoiceInput
 * @author coder
 *
 * Created on: May 27, 2025 at 3:13:28 PM
 */

namespace gui\v2\components\inputs {

    use gui\v2\Component;
    use gui\v2\decorators\Direction;

    final class ChoiceInput extends Input
    {

        private const CSS_CLASS = 'choice';

        private readonly bool $type;
        private readonly string $name;

        /**
         * Create an input group with two or more choices
         * @param string $name Input group name
         * @param bool $multiselect True for multiselect (checkbox)
         * @param Direction $dir Direction
         */
        public function __construct(string $name, bool $multiselect, Direction $dir = Direction::HORIZONTAL)
        {
            parent::__construct('div');
            $this->name = $name;
            $this->type = $multiselect ? 'checkbox' : 'radio';
            $this->classList->addAll([self::CSS_CLASS . '-group', 'size-md', $dir->value]);
        }

        /**
         * Create a choice
         * @param string $label Label text
         * @param string|float|null $value Input value
         * @param bool $selected Whether the input is selected by default or not
         * @return self
         */
        public function addChoice(string $label, string|float|null $value, bool $selected = false): self
        {
            $inputLabel = new Component('label');
            $span = new Component('span');
            $span->setText($label);
            $input = new Component('input');
            $input->classList->addOne(self::CSS_CLASS);
            $input->attribute->addAll([
                'name' => $this->name, 'type' => $this->type,
                'value' => $value
            ]);
            if ($selected) {
                $input->attribute->addOne('checked', null);
            }
            $inputLabel->appendChild($input, $span);
            $this->appendChild($inputLabel);
            return $this;
        }
    }

}
