<?php

/**
 * Description of Carousel
 * @author coder
 *
 * Created on: May 12, 2024 at 4:33:17 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Carousel extends Component
    {

        private const NAME = 'carousel';

        private Component $slides;
        private bool $bottomNav;
        private array $slideIds = [];

        public function __construct(bool $bottomNav = true)
        {
            parent::__construct('div');
            $this->addProps([self::NAME]);
            $this->bottomNav = $bottomNav;
            $this->slides = new Component('ul', false);
            $this->slides->addProps([self::NAME . '-slides']);
        }

        public function addItem(Component ...$items): self
        {
            foreach ($items as $item) {
                $id = 'id' . rand(100, 10000);
                $slide = new Component('li', false);
                $slide->appendChildren($item);
                $slide->setAttr('id', $id);
                $this->slides->appendChildren($slide);
                $this->slideIds[] = $id;
            }
        }

        private function makeNavigation(int $count): self
        {
            $pos = $this->bottomNav ? parent::POS_BC : parent::POS_TC;
            $nav = new Component('ul', false);
            $nav->addProps([self::NAME . '-nav'])->setPosition($pos);
            for ($i = 0; $i < $count; $i++) {
                $dot = new Component('li', '&nbsp;');
                $dot->setAttr('for', $this->slideIds[$i]);
                $nav->appendChildren($dot);
            }
            $prevBtn = new ActionButton(ActionButton::TYPE_PREV);
            $nextBtn = new ActionButton(ActionButton::TYPE_NEXT);
            $nextBtn->setPosition(parent::POS_CR);
            $prevBtn->setPosition(parent::POS_CL);
            $this->appendChildren($prevBtn, $nextBtn, $nav);
            return $this;
        }

        public function build(): string
        {
            $this->appendChildren($this->slides);
            $kids = $this->slides->children();
            if (count($kids) > 1) {
                $this->makeNavigation($kids);
            }
            return parent::build();
        }
    }

}
