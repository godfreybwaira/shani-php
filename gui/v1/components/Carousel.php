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

        private Component $items, $nav;

        public function __construct()
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
            $this->items = new Component('ul');
            $this->nav = new Component('div');
            $this->items->setProps([self::NAME . '-items']);
            $this->nav->setProps([self::NAME . '-nav']);
        }

        public function addItems(Component ...$items): self
        {
            foreach ($items as $item) {
                $listItem = new Component('li');
                $listItem->appendChildren($item);
                $this->items->appendChildren($listItem);
            }
        }

        public function build(): string
        {
            $this->appendChildren($this->items, $this->nav);
            return parent::build();
        }
    }

}
