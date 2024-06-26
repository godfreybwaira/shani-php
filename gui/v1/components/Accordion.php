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
            $this->addProperty(self::NAME);
            if ($openMultiple) {
                $this->addProperty(self::NAME . '-multiopen');
            }
        }

        public function addItem(string $title, Component $body, bool $open = false, bool $disabled = false): self
        {
            $item = new Component('li', false);
            $node = new Component('a', false);
            $node->setContent($title);
            $wrapper = new Component('div', false);
            $item->addProperty(self::NAME . '-item');
            $wrapper->addProperty(self::NAME . '-body');
            $node->addProperty(self::NAME . '-title')->setAttribute('href', '#');
            $wrapper->appendChildren($body);
            $item->appendChildren($node, $wrapper);
            if ($open) {
                $item->addClass('active');
            }
            if ($disabled) {
                $item->setAttribute('disabled');
            }
            return $this->appendChildren($item);
        }
    }

}
