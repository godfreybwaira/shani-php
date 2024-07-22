<?php

/**
 * ChoiceInput is a form input that enable multiple choice selection on available
 * choices.
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ChoiceInput extends Component
    {

        private const CHOICE_INPUT = 0;
        private const PROPS = [
            self::CHOICE_INPUT => ''
        ];

        private bool $wrapped = false;
        private ?InputGroup $group = null;
        private string $name, $type = 'radio';

        public function __construct(string $name, bool $multiSelect = false)
        {
            parent::__construct('ul', self::PROPS);
            $this->name = $name;
            if ($multiSelect) {
                $this->type = 'checkbox';
            }
            $this->addStyle(self::CHOICE_INPUT);
        }

        /**
         * Add input component
         * @param mixed $value Input value
         * @param string|null $label Input label
         * @return self
         */
        public function addItem(mixed $value, ?string $label = null): self
        {
            $listItem = new Component('li');
            $input = new Component('input');
            $input->setAttribute('type', $this->type)->setAttribute('name', $this->name);
            $id = 'id' . hrtime(true);
            $input->setAttribute('id', $id)->setAttribute('value', $value);
            $choiceLabel = new Component('label');
            $choiceLabel->setContent($label ?? $value)->setAttribute('for', $id);
            $listItem->appendChildren($input, $choiceLabel);
            return $this->appendChildren($listItem);
        }

        public function setParent(InputGroup $parent): self
        {
            $this->group = $parent;
            return $this;
        }

        public function build(): string
        {
            if ($this->group !== null && !$this->wrapped) {
                $this->wrapped = true;
                return $this->group->appendChildren($this)->build();
            }
            return parent::build();
        }
    }

}
