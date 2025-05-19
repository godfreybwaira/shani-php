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

    final class Tab extends Component
    {

        private readonly Component $menuItem;
        private readonly Component $body;

        public function __construct(TabPosition $pos = TabPosition::TOP)
        {
            parent::__construct('div');
            $this->classList->addAll(['tab', $pos->value]);
            $this->body = new Component();
            $this->menuItem = new Component('ul');
            $menuName = $this->menuItem->getUniqueName();
            $bodyName = $this->body->getUniqueName();
            $this->style->addOne('grid-template-areas', self::getTemplateArea($pos, $menuName, $bodyName));
            $this->menuItem->style->addOne('grid-area', $menuName);
            $this->menuItem->classList->addOne('tab-menu');
            $this->body->style->addOne('grid-area', $bodyName);
            $this->body->classList->addAll(['tab-body', 'padding-xy']);
            $this->appendChild($this->menuItem);
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
        public function addMenuItem(MenuItem $item): self
        {
            $this->menuItem->appendChild($item);
            return $this;
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
