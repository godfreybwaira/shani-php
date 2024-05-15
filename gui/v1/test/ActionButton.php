<?php

/**
 * Description of ActionButton
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\components {

    final class ActionButton extends \gui\v1\Component
    {

        private const NAME = 'action-btn';
        private const TYPES = ['prev', 'next', 'times', 'maximize', 'plus'];
        public const TYPE_PREV = 0, TYPE_NEXT = 1, TYPE_TIMES = 2, TYPE_MAXIMIZE = 3, TYPE_PLUS = 4;

        private string $type;

        public function __construct(int $type, string $text = null)
        {
            parent::__construct('button', $text);
            $this->setProps([self::NAME])->setType($type);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }
    }

}
