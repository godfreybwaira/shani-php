<?php

/**
 * Description of Steps
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Steps extends Component
    {

        private const NAME = 'steps';

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME)->addProperty(self::NAME, 'round');
        }

        public function addItem(Component $item, bool $current, bool $complete = false): self
        {
            $list = new Component('li', false);
            if ($complete) {
                $list->addProperty(self::NAME, 'complete');
            } elseif ($current) {
                $list->addProperty(self::NAME, 'current');
            }
            $list->appendChildren($item);
            return $this->appendChildren($list);
        }
    }

}
