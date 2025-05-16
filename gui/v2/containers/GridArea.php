<?php

/**
 * Description of GridArea
 * @author coder
 *
 * Created on: May 16, 2025 at 10:48:26 AM
 */

namespace gui\v2\containers {

    use gui\v2\Component;

    final class GridArea extends Component
    {

        private readonly string $name;

        public function __construct()
        {
            parent::__construct('div');
            $this->name = 'd' . substr(hrtime(true), 8);
            $this->style->addOne('grid-area', $this->name);
        }

        public function getName(): string
        {
            return $this->name;
        }
    }

}
