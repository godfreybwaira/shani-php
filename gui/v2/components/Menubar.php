<?php

/**
 * Description of MenuItem
 * @author coder
 *
 * Created on: May 19, 2025 at 11:55:16 AM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\Direction;

    final class Menubar extends Component
    {

        /**
         * Create a menubar with clickable menu items
         * @param Direction $direction The way items on a menu flows
         * @param bool $hideLabels Whether to show labels on menu items or not
         */
        public function __construct(Direction $direction, bool $hideLabels = false)
        {
            parent::__construct('div');
            $this->hideLabels = $hideLabels;
            if ($hideLabels) {
                $this->classList->addOne('menubar-hide-labels');
            }
            $this->classList->addAll(['menubar', $direction->value]);
        }

        /**
         * Add menu item
         * @param MenuItem $items Menu item(s)
         * @return self
         */
        public function addMenu(MenuItem ...$items): self
        {
            foreach ($items as $menu) {
                $this->appendChild($menu);
            }
            return $this;
        }
    }

}
