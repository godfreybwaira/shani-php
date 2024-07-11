<?php

/**
 * Stepper is a component that guides users through a multi-step process, such as
 * a registration, checkout, or setup wizard. It breaks down complex tasks into
 * smaller, sequential steps, making it easier for users to complete them without
 * feeling overwhelmed.
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Stepper extends Component
    {

        private const NAME = 'steps';

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME)->addProperty(self::NAME, 'round');
        }

        /**
         * Add child(ren)
         * @param Component $item
         * @param bool $active If sets to true, the current item becomes the active step
         * @param bool $complete Whether the step is completed or not
         * @return self
         */
        public function addItem(Component $item, bool $active, bool $complete = false): self
        {
            $list = new Component('li', false);
            if ($complete) {
                $list->addProperty(self::NAME, 'complete');
            } elseif ($active) {
                $list->addProperty(self::NAME, 'active');
            }
            $list->appendChildren($item);
            return $this->appendChildren($list);
        }
    }

}
