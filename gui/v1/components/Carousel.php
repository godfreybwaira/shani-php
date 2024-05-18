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
            $this->addProperty(self::NAME);
            $this->bottomNav = $bottomNav;
            $this->slides = new Component('ul', false);
            $this->slides->addProperty(self::NAME, 'slides');
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
            $nav->addProperty(self::NAME, 'nav');
            for ($i = 0; $i < $count; $i++) {
                $dot = new Component('li', '&nbsp;');
                $dot->setAttr('for', $this->slideIds[$i]);
                $nav->appendChildren($dot);
            }
            $nextBtn = new ActionButton(ActionButton::TYPE_NEXT);
            $prevBtn = new ActionButton(ActionButton::TYPE_PREV);
            $nextBtn->setPosition(parent::POS_CR)->setParent($this);
            $prevBtn->setPosition(parent::POS_CL)->setParent($this);
            $this->appendChildren($nav->setPosition($pos));
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
