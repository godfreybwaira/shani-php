<?php

/**
 * Description of DropDownMenu
 * @author coder
 *
 * Created on: May 28, 2025 at 12:24:56 PM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\DropdownPosition;

    final class DropdownMenu extends Component
    {

        private const CSS_CLASS = 'dropdown';

        private readonly Component $body;

        /**
         * Create dropdown menu
         * @param Component $label Menu label
         * @param DropdownPosition $pos Menu body position
         */
        public function __construct(Component $label, DropdownPosition $pos)
        {
            parent::__construct('div');
            $this->body = new Component();
            $this->appendChild($label, $this->body);
            $this->classList->addOne(self::CSS_CLASS);
            $this->body->classList->addAll([self::CSS_CLASS . '-body', $pos->value, 'shadow-sm']);
        }

        /**
         * Get drop-down menu body (content)
         * @return Component
         */
        public function getBody(): Component
        {
            return $this->body;
        }
    }

}
