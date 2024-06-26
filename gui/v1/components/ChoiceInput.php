<?php

/**
 * Description of ChoiceInput
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ChoiceInput extends Component
    {

        private const NAME = 'choice-input';

        private bool $wrapped = false;
        private ?InputGroup $group = null;
        private string $name, $type = 'radio';

        public function __construct(string $name, bool $multiSelect = false)
        {
            parent::__construct('ul');
            $this->name = $name;
            if ($multiSelect) {
                $this->type = 'checkbox';
            }
            $this->addProperty(self::NAME);
        }

        public function addItem(mixed $value, ?string $text = null): self
        {
            $listItem = new Component('li');
            $input = new Component('input');
            $input->setAttribute('type', $this->type)->setAttribute('name', $this->name);
            $id = 'id' . hrtime(true);
            $input->setAttribute('id', $id)->setAttribute('value', $value)->setMargin(null);
            $label = new Component('label', $text ?? $value);
            $label->setAttribute('for', $id)->setMargin(null);
            $listItem->appendChildren($input, $label)->setGutters(null);
            return $this->appendChildren($listItem);
        }

        public function setInputGroup(InputGroup $group): self
        {
            $this->group = $group;
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
