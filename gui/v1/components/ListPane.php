<?php

/**
 * Description of ListPane
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ListPane extends Component
    {

        private const NAME = 'list-pane', STRIPES = ['even', 'odd'];
        public const STRIPES_EVEN = 0, STRIPES_ODD = 1;

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME);
        }

        public function setStripes(int $stripes): self
        {
            return $this->addProperty(self::NAME . '-stripes', self::STRIPES[$stripes]);
        }

        public function addItem(Component ...$items): self
        {
            $list = new Component('li', false);
            foreach ($items as $item) {
                $list->appendChildren($item);
            }
            return $this->appendChildren($list);
        }
    }

}
