<?php

/**
 * Description of InputGroup
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

        public function setType(int $type): self
        {
            $this->addProperty('input-type', self::TYPES[$type]);
            return $this;
        }

        public function setMask(string $text): self
        {
            $mask = new Component('span', false);
            $mask->addProperty('input-mask')->setContent($text)->setPadding(parent::SIZE_DEFAULT);
            $this->appendChildren($mask);
            return $this;
        }

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
