<?php

/**
 * Description of Accordion
 * @author coder
 *
 * Created on: May 13, 2024 at 11:47:22 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Accordion extends Component
    {

        private const NAME = 'accordion';

        public function __construct(bool $openMultiple = false)
        {
            parent::__construct('ul');
            $size = parent::SIZE_MD;
            $this->setProps([self::NAME])->setPadding($size)->setFontSize($size);
            if ($openMultiple) {
                $this->setProps([self::NAME . '-multiopen']);
            }
        }

        public function addItem(string $title, Component $body, bool $open = false, bool $disabled = false): self
        {
            $item = new Component('li');
            $node = new Component('a', $title);
            $wrapper = new Component('div');
            $item->setProps([self::NAME . '-item']);
            $wrapper->setProps([self::NAME . '-body']);
            $node->setProps([self::NAME . '-title'])->setAttr('href', '#');
            $wrapper->appendChildren($body);
            $item->appendChildren($node, $wrapper);
            if ($open) {
                $item->addClass('active');
            }
            if ($disabled) {
                $item->setAttr('disabled');
            }
            return $this->appendChildren($item);
        }
    }

}
