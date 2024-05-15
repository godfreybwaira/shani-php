<?php

/**
 * Description of Card
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Card extends Component
    {

        private const NAME = 'card';

        private ?Component $header, $body, $footer;

        public function __construct()
        {
            parent::__construct('div');
            $size = \gui\v1\Theme::DEFAULT_SIZE;
            $this->setProps([self::NAME])->setPadding($size)->setFontSize($size);
            $this->header = $this->body = $this->footer = null;
        }

        public function setHeader(Component $header): self
        {
            $this->header = $header;
            $this->header->setProps([self::NAME . '-header']);
            return $this;
        }

        public function setBody(Component $body): self
        {
            $this->body = $body;
            $this->header->setProps([self::NAME . '-body']);
            return $this;
        }

        public function setAlign(bool $horizontal): self
        {
            if ($horizontal) {
                return $this->setProps([self::NAME . '-h']);
            }
            return $this;
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }

        public function setFooter(Component $footer): self
        {
            $this->footer = $footer;
            $this->header->setProps([self::NAME . '-footer']);
            return $this;
        }

        public function build(): string
        {
            $this->appendChildren($this->header, $this->body, $this->footer);
            return parent::build();
        }
    }

}
