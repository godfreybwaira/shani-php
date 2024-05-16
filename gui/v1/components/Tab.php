<?php

/**
 * Description of Tab
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Tab extends Component
    {

        private bool $wrapped = false;

        private const NAME = 'tab';

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function addLink(Component $link, bool $active = false): self
        {
            $list = new Component('li', false);
            if ($active) {
                $list->addClass('active');
            }
            $list->appendChildren($link);
            $this->appendChildren($list);
        }

        public function setAlign(bool $vertical): self
        {
            if ($vertical) {
                return $this->setProps([self::NAME . '-align-v']);
            }
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                $container = new Component('div', false);
                $body = new Component('div', false);
                return $container->appendChildren($this, $body)->build();
            }
            return parent::build();
        }
    }

}
