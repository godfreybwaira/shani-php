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

        private string $size, $type;
        private bool $wrapped = false;

        public function __construct()
        {
            parent::__construct('div', false);
            $this->size = parent::SIZES[parent::SIZE_DEFAULT];
            $this->type = self::TYPES[self::TYPE_SOLID];
            $this->setProps([self::NAME]);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function setSize(?int $size): self
        {
            $this->size = parent::SIZES[$size ?? self::SIZE_FULL];
            return $this;
        }

        private static function wrap(self $modal): Component
        {
            $container = new Component('div', false);
            $container->setProps([self::NAME . '-container']);
            $nav = new Component('ul', false);
            $nav->setProps([self::NAME . '-nav']);

            $times = new ActionButton(ActionButton::TYPE_TIMES);
            $maximize = new ActionButton(ActionButton::TYPE_MAXIMIZE);
            $listMax = new Component('li', false);
            $listTimes = new Component('li', false);
            $nav->appendChildren($listMax->appendChildren($maximize), $listTimes->appendChildren($times));
            return $container->appendChildren($nav, $modal);
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return self::wrap($this)->build();
            }
            $this->setProps([self::NAME . '-' . $this->size, self::NAME . '-type-' . $this->type]);
            return parent::build();
        }
    }

}
