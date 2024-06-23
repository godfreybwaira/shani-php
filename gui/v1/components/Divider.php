<?php

/**
 * Description of Divider
 * @author coder
 *
 * Created on: May 12, 2024 at 11:42:47 AM
 */

namespace gui\v1\components {

    final class Divider extends \gui\v1\Component
    {

        private const NAME = 'divider';

        public function __construct(string $text = null)
        {
            parent::__construct('div');
            $this->setContent($text)->addProperty(self::NAME);
        }
    }

}
