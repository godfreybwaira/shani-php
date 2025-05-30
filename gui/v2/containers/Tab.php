<?php

/**
 * Description of Tab
 * @author coder
 *
 * Created on: May 18, 2025 at 11:36:54 PM
 */

namespace gui\v2\containers {

    use gui\v2\Component;
    use gui\v2\components\Menubar;
    use gui\v2\decorators\TabPosition;

    final class Tab extends Component
    {

        private readonly Component $body;
        private readonly Menubar $menubar;

        public function __construct(Menubar $menubar, TabPosition $pos = TabPosition::TOP)
        {
            parent::__construct('div');
            $this->menubar = $menubar;
            $this->body = new Component();
            $this->appendChild($this->menubar);
            $this->menubar->classList->addOne('tab-menu');
            $this->body->classList->addAll(['tab-body', 'padding-xy']);
            $this->classList->addAll(['tab', 'tab-' . $pos->value]);
        }

        /**
         * Get Tab body (content)
         * @return Component
         */
        public function getBody(): Component
        {
            return $this->body;
        }

        /**
         * Get Tab menu
         * @return Menubar
         */
        public function getMenu(): Menubar
        {
            return $this->menubar;
        }

        public function open(): string
        {
            $tab = parent::open();
            $this->appendChild($this->body);
            return $tab . $this->body->open();
        }

        public function close(): string
        {
            return $this->body->close() . parent::close();
        }
    }

}
