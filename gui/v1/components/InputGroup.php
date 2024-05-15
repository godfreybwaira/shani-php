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

        private const NAME = 'input-group', TYPES = ['type-1', 'type-2'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        private ?string $type = null;

        public function __construct()
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
            $this->setType(self::TYPE_1);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function setMask(string $text): self
        {
            $mask = new Component('span', $text);
            $mask->setProps(['input-mask']);
            $this->appendChildren($mask);
            return $this;
        }

        public function setInput(string $type, string $name, ?string $placeholder = null): self
        {
            $tag = ($type === 'select' || $type === 'textarea') ? $type : 'input';
            $input = new Component($tag);
            $input->setAttr('type', $type)->setAttr('name', $name)->setAttr('id', $name);
            if ($placeholder !== null) {
                $input->setAttr('placeholder', $placeholder);
            }
            $this->appendChildren($input);
            return $this;
        }

        public function setLabel(string $text, ?string $refId = null): self
        {
            $label = new Component('label', $text);
            if ($refId !== null) {
                $label->setAttr('for', $refId);
            }
            $this->appendChildren($label);
            return $this;
        }

        public function build(): string
        {
            $this->setProps(['input-' . $this->type]);
            return parent::build();
        }
    }

}
