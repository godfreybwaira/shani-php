<?php

/**
 * Description of Modal
 * @author coder
 *
 * Created on: May 16, 2024 at 10:09:03 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Modal extends Component
    {

        private const NAME = 'modal', TYPES = ['card', 'drawer'];
        public const TYPE_DRAWER = 1, TYPE_CARD = 0;

        private bool $wrapped = false;
        private ?Component $navbar = null;
        private Component $wrapper;

        public function __construct()
        {
            parent::__construct('div', false);
            $this->addProperty(self::NAME);
            $this->setType(self::TYPE_CARD);
            $this->wrapper = new Component('div', false);
            $this->wrapper->addProperty(self::NAME . '-wrapper');
        }

        public function setType(int $type): self
        {
            return $this->addProperty(self::NAME . '-type', self::TYPES[$type]);
        }

        public function addNavbar(Component ...$items): self
        {
            if ($this->navbar === null) {
                $this->navbar = new Component('ul', false);
                $this->navbar->addProperty(self::NAME . '-nav');
                $this->wrapper->appendChildren($this->navbar);
            }
            foreach ($items as $item) {
                $list = new Component('li', false);
                $list->appendChildren($item);
            }
            $this->navbar->appendChildren($list);
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return $this->wrapper->appendChildren($this)->build();
            }
            return parent::build();
        }
    }

}
