<?php

/**
 * Description of Tab
 * @author coder
 *
 * Created on: May 18, 2025 at 11:36:54 PM
 */

namespace gui\v2\containers {

    use gui\v2\Component;
    use gui\v2\components\MenuItem;
    use gui\v2\decorators\TabPosition;

    final class Tab extends Component
    {

        private readonly Component $menu, $body;

        public function __construct(TabPosition $pos = TabPosition::TOP)
        {
            parent::__construct('div');
            $this->classList->addAll(['tab', 'tab-' . $pos->value]);
            $this->body = new Component();
            $this->menu = new Component();
            $menuName = $this->menu->getUniqueName();
            $bodyName = $this->body->getUniqueName();
            $this->style->addOne('grid-template-areas', self::getTemplateArea($pos, $menuName, $bodyName));
            $this->menu->style->addOne('grid-area', $menuName);
            $this->menu->classList->addOne('tab-menu');
            $this->body->style->addOne('grid-area', $bodyName);
            $this->body->classList->addAll(['tab-body', 'padding-xy']);
            $this->appendChild($this->menu);
        }

        private static function getTemplateArea(TabPosition $pos, string $menuName, string $bodyName): string
        {
            $area = match ($pos) {
                TabPosition::TOP => $menuName . '""' . $bodyName,
                TabPosition::BOTTOM => $bodyName . '""' . $menuName,
                TabPosition::LEFT => $menuName . ' ' . $bodyName,
                TabPosition::RIGHT => $bodyName . ' ' . $menuName,
            };
            return '"' . $area . '"';
        }

        /**
         * Add a new tab as menu item
         * @param MenuItem $item A tab
         * @return self
         */
        public function addMenuItem(MenuItem ...$items): self
        {
            foreach ($items as $item) {
                $this->menu->appendChild($item);
            }
            return $this;
        }

        public function getBody(): Component
        {
            return $this->body;
        }

        public function getMenu(): Component
        {
            return $this->menu;
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
