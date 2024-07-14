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

        private const STEPPER = 0, STEPPER_ROUND = 1, STEPPER_COMPLETE = 2;
        private const PROPS = [
            self::STEPPER => '',
            self::STEPPER_ROUND => '',
            self::STEPPER_COMPLETE => ''
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addProperty(self::STEPPER)->addProperty(self::STEPPER_ROUND);
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
            $list = new Component('li', self::PROPS);
            if ($complete) {
                $list->addProperty(self::STEPPER_COMPLETE);
            } elseif ($active) {
                $list->setActive($active);
            }
            $list->appendChildren($item);
            return $this->appendChildren($list);
        }
    }

}
