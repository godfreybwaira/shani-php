<?php

/**
 * Carousel also known as a slider or slideshow, is a user interface component
 * that displays a collection of items, such as images or cards, in a rotating
 * fashion, one at a time. Users can navigate through the items manually or automatically.
 * @author coder
 *
 * Created on: May 12, 2024 at 4:33:17 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Carousel extends Component
    {

        private const CAROUSEL = 0, CAROUSEL_SLIDES = 1, CAROUSEL_NAV = 2;
        private const ANIMATION_BEHAVIOR = 3;
        private const PROPS = [
            self::CAROUSEL => '',
            self::CAROUSEL_SLIDES => '',
            self::CAROUSEL_NAV => '',
            self::ANIMATION_BEHAVIOR => [
                Style::ANINATION_FADE => '',
                Style::ANINATION_SLIDE_BOTTOM => '',
                Style::ANINATION_SLIDE_LEFT => '',
                Style::ANINATION_SLIDE_RIGHT => '',
                Style::ANINATION_SLIDE_TOP => ''
            ]
        ];

        private Component $slides;
        private bool $bottomNav;
        private array $slideIds = [];

        public function __construct(bool $bottomNav = true)
        {
            parent::__construct('div', self::PROPS);
            $this->addStyle(self::CAROUSEL);
            $this->bottomNav = $bottomNav;
            $this->slides = new Component('ul', self::PROPS);
            $this->slides->addStyle(self::CAROUSEL_SLIDES);
        }

        /**
         * Add a child item to a carousel
         * @param Component $items Item(s) to add
         * @return self
         */
        public function addItem(Component ...$items): self
        {
            foreach ($items as $item) {
                $id = static::createId();
                $slide = new Component('li', self::PROPS);
                $slide->appendChildren($item);
                $slide->setAttribute('id', $id);
                $this->slides->appendChildren($slide);
                $this->slideIds[] = $id;
            }
        }

        private function makeNavigation(int $count): self
        {
            $pos = $this->bottomNav ? Style::POS_BC : Style::POS_TC;
            $nav = new Component('ul', self::PROPS);
            $nav->addStyle(self::CAROUSEL_NAV);
            for ($i = 0; $i < $count; $i++) {
                $dot = new Component('li');
                $dot->setAttribute('for', $this->slideIds[$i])->setText('&nbsp;');
                $nav->appendChildren($dot);
            }
            $nextBtn = new ActionButton(ActionButton::TYPE_NEXT);
            $prevBtn = new ActionButton(ActionButton::TYPE_PREV);
            $nextBtn->setPosition(Style::POS_CR)->setParent($this);
            $prevBtn->setPosition(Style::POS_CL)->setParent($this);
            $this->appendChildren($nav->setPosition($pos));
            return $this;
        }

        /**
         * Set animation behavior
         * @param int $behavior Animation behavior set using Style::ANIMATION_*
         * @return self
         */
        public function setAnimationBehavior(int $behavior): self
        {
            $this->addStyle(self::ANIMATION_BEHAVIOR, $behavior);
            return $this;
        }

        public function build(): string
        {
            $this->appendChildren($this->slides);
            $kids = $this->slides->getChildren();
            if (count($kids) > 1) {
                $this->makeNavigation($kids);
            }
            return parent::build();
        }
    }

}
