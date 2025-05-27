<?php

/**
 * Description of Carousel
 * @author coder
 *
 * Created on: May 27, 2025 at 9:14:34 AM
 */

namespace gui\v2\containers\lists {

    use gui\v2\Component;

    final class Carousel extends Component
    {

        private const CSS_CLASS = 'carousel';

        private readonly Component $prev, $next, $body;

        public function __construct()
        {
            parent::__construct('div');
            $this->body = new Component('ul');
            $this->prev = new Component('button');
            $this->next = new Component('button');
            $this->prev->classList->addOne(self::CSS_CLASS . '-prev');
            $this->next->classList->addOne(self::CSS_CLASS . '-next');
            $this->body->classList->addAll([self::CSS_CLASS . '-body', 'shadow-sm']);
            $this->appendChild($this->prev);
        }

        /**
         * Get carousel "previous" button
         * @return Component
         */
        public function getPrevButton(): Component
        {
            return $this->prev;
        }

        /**
         * Get carousel "next" button
         * @return Component
         */
        public function getNextButton(): Component
        {
            return $this->next;
        }

        /**
         * Get carousel body (content)
         * @return Component
         */
        public function getBody(): Component
        {
            return $this->body;
        }

        /**
         * Add list item created using LI tag (child of UL)
         * @param Component $item List item
         * @return self
         * @throws \Exception
         */
        public function addItem(Component $item): self
        {
            if ($item->getTag() !== 'li') {
                throw new \Exception('Invalid list item. Must has "li" tag.');
            }
            $this->body->appendChild($item);
            return $this;
        }

        public function open(): string
        {
            $carousel = parent::open();
            $this->appendChild($this->body, $this->next);
            return $carousel . $this->body->open();
        }

        public function close(): string
        {
            return $this->body->close() . $this->next->build() . parent::close();
        }
    }

}
