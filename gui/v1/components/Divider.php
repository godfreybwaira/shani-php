<?php

/**
 * Divider is a visual or functional element used to separate content, categories,
 * or sections within a webpage.
 * @author coder
 *
 * Created on: May 12, 2024 at 11:42:47 AM
 */

namespace gui\v1\components {

    final class Divider extends \gui\v1\Component
    {

        private const DIVIDER = 0;
        private const PROPS = [
            self::DIVIDER => ''
        ];

        public function __construct(string $text = null)
        {
            parent::__construct('div', self::PROPS);
            $this->setContent($text)->addProperty(self::DIVIDER);
        }
    }

}
