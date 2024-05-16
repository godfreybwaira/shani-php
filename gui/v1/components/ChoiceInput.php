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

        private string $name;
        private InputGroup $group;
        private bool $wrapped = false;
        private string $type = 'radio';

        public function __construct(string $name, bool $multiSelect = false)
        {
            parent::__construct('ul');
            $this->name = $name;
            $this->group = new InputGroup();
            if ($multiSelect) {
                $this->type = 'checkbox';
            }
            $this->setProps([self::NAME]);
        }

        public function addItem(mixed $value, ?string $text = null): self
        {
            $listItem = new Component('li');
            $input = new Component('input');
            $input->setAttr('type', $this->type)->setAttr('name', $this->name);
            $id = 'id' . rand(100, 10000);
            $input->setAttr('id', $id)->setAttr('value', $value)->setGap(null);
            $label = new Component('label', $text ?? $value);
            $label->setAttr('for', $id)->setGap(null);
            $listItem->appendChildren($input, $label)->setSize(null);
            return $this->appendChildren($listItem);
        }

        public function setType(int $type): self
        {
            $this->group->setType($type);
            return $this;
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }

        public function setSize(int $size): self
        {
            $this->group->setSize($size);
            return $this;
        }

        public function setMask(string $label): self
        {
            $this->group->setMask($label);
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return $this->group->appendChildren($this)->build();
            }
            return parent::build();
        }
    }

}
