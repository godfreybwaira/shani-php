<?php

/**
 * Description of Button
 * @author coder
 *
 * Created on: Apr 20, 2025 at 7:51:35 PM
 */

namespace gui\v2\controls {

    use gui\v2\Component;
    use gui\v2\decoration\themes\FlatButtonTheme;

    final class FlatButton extends Component
    {

        public function __construct(string $text)
        {
            parent::__construct('button');
            $this->setText($text);
            $this->setTheme(FlatButtonTheme::getDefaultTheme());
        }

        public function changeTheme(FlatButtonTheme $theme): self
        {
            $this->setTheme($theme);
            return $this;
        }
    }

}
