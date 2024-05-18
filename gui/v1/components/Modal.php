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

        private const NAME = 'modal', TYPES = ['solid', 'drawer'];
        public const TYPE_DRAWER = 1, TYPE_SOLID = 0;

        private string $type;
        private bool $wrapped = false;

        public function __construct()
        {
            parent::__construct('div', false);
            $this->addProperty(self::NAME);
            $this->setColumnSize(8, parent::SIZE_DEFAULT);
            $this->setType(self::TYPE_SOLID);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        private static function wrap(self $modal): Component
        {
            $wrapper = new Component('div', false);
            $wrapper->addProperty(self::NAME, 'wrapper');
            $nav = new Component('ul', false);
            $nav->addProperty(self::NAME, 'nav');

            $times = new ActionButton(ActionButton::TYPE_TIMES);
            $maximize = new ActionButton(ActionButton::TYPE_MAXIMIZE);
            $listMax = new Component('li', false);
            $listTimes = new Component('li', false);
            $nav->appendChildren($listMax->appendChildren($maximize), $listTimes->appendChildren($times));
            return $wrapper->appendChildren($nav, $modal);
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return self::wrap($this)->build();
            }
            $this->addProperty(self::NAME, 'type-' . $this->type);
            return parent::build();
        }
    }

}
