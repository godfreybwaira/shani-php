<?php

/**
 * Description of Dropdown
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Dropdown extends Component
    {

        public const ALIGN_HORIZONTAL = 0;

        private Component $body;

        private const NAME = 'dropdown';

        public function __construct(string $text = null)
        {
            parent::__construct('div', $text);
            $this->setProps([self::NAME]);
        }

        public function setBody(Component $body): self
        {
            $this->body = $body;
            $this->body->setProps([self::NAME . '-body']);
            return $this;
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }

        public function build(): string
        {
            $this->appendChildren($this->body);
            return parent::build();
        }
    }

}
