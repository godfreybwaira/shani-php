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

namespace gui\v1\widgets {

    use gui\v1\Component;

    final class Stepper extends Component
    {

        private const STEPPER = 0, STEPPER_ROUND = 1, STEPPER_COMPLETE = 2;

        /**
         * Represent an active step
         */
        public const STATUS_ACTIVE = 0;

        /**
         * Representing a complete step
         */
        public const STATUS_COMPLETE = 1;
        private const PROPS = [
            self::STEPPER => '',
            self::STEPPER_ROUND => '',
            self::STEPPER_COMPLETE => ''
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addStyle(self::STEPPER)->addStyle(self::STEPPER_ROUND);
        }

        /**
         * Add child(ren)
         * @param Component $item
         * @param int $status Stepper status set using Stepper::STATUS_*
         * @return self
         */
        public function addItem(Component $item, int $status): self
        {
            $list = new Component('li', self::PROPS);
            if ($status == self::STATUS_COMPLETE) {
                $list->addStyle(self::STEPPER_COMPLETE);
            } else if ($status === self::STATUS_ACTIVE) {
                $list->setActive(true);
            }
            $list->appendChildren($item);
            return $this->appendChildren($list);
        }
    }

}
