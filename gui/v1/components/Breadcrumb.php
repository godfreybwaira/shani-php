<?php

/**
 * Description of Breadcrumb
 * @author coder
 *
 * Created on: May 12, 2024 at 11:21:13 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Breadcrumb extends Component
    {

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProps(['breadcrumb']);
        }

        public function addItem(Component $item): self
        {
            $listItem = new Component('li', false);
            $listItem->appendChildren($item);
            $this->appendChildren($listItem);
        }
    }

}
