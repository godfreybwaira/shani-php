<?php

/**
 * Description of Row
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Row extends Component
    {

        private const NAME = 'row';

        public function __construct()
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
        }

        public function setWrap(): self
        {
            return $this->setProps([self::NAME . '-wrap']);
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }
    }

}
