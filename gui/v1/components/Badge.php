<?php

/**
 * Description of Badge
 * @author coder
 *
 * Created on: May 12, 2024 at 10:17:47 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Badge extends Component
    {

        private const NAME = 'badge';

        public function __construct(string $text = null)
        {
            parent::__construct('span');
            $this->setContent($text)->addProperty(self::NAME);
            if (parent::SIZE_DEFAULT === parent::SIZE_SM) {
                $this->setGutters(parent::SIZE_SM);
            } else {
                $this->setGutters(parent::SIZE_DEFAULT - 1);
            }
        }
    }

}
