<?php

/**
 * Description of ListItem
 * @author coder
 *
 * Created on: May 12, 2024 at 10:28:17 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ListItem extends Component
    {

        private ?Component $title, $body = null;

        public function __construct(string $title)
        {
            parent::__construct('li');
            $this->title = new Component('a', $title);
            $this->title->setAttr('href', '#');
            $this->appendChildren($this->title);
        }

        public function title(): Component
        {
            return $this->title;
        }

        public function &body(): Component
        {
            return $this->body;
        }

        public function setBody(Component $body): self
        {
            $this->body = $body;
            return $this;
        }

        public function build(): string
        {
            if ($this->body !== null) {
                $this->appendChildren($this->body);
            }
            return parent::build();
        }
    }

}
