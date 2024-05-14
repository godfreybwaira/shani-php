<?php

/**
 * Description of Toast
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Toast extends Component
    {

        private const NAME = 'toast';

        private ?Component $header, $body;

        public function __construct()
        {
            parent::__construct('div');
            $this->header = $this->body = null;
            $this->setProps([self::NAME]);
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

        public function build(): string
        {
            $this->appendChildren($this->header, $this->body);
            return parent::build();
        }
    }

}
