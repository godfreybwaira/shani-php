<?php

/**
 * Description of Table
 * @author coder
 *
 * Created on: May 6, 2024 at 10:40:52 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Table extends Component
    {

        private bool $wrapped = false;
        private ?Component $header, $body, $footer, $caption = null;

        public function __construct(string $caption = null)
        {
            parent::__construct('table');
            $this->header = $this->body = $this->footer = null;
            $this->setCaption($caption)->addProperty('table');
        }

        private static function wrap(Table $table): Component
        {
            $wrapper = new Component('div', false);
            $wrapper->addProperty('table-wrapper');
            return $wrapper->appendChildren($table);
        }

        public function setCaption(?string $caption): self
        {
            if (!$this->caption && $caption !== null) {
                $this->caption = new Component('caption', false);
                $this->caption->setContent($caption);
            }
            return $this;
        }

        public function setRow(Component ...$td): self
        {
            return $this->setData('tbody', $this->body, ...$td);
        }

        public function setHeader(Component ...$th): self
        {
            return $this->setData('thead', $this->header, ...$th);
        }

        public function setFooter(Component ...$td): self
        {
            return $this->setData('tfoot', $this->footer, ...$td);
        }

        private function setData(string $tag, ?Component &$wrapper, Component ...$cells): self
        {
            if (!$wrapper) {
                $wrapper = new Component($tag, false);
            }
            $row = new Component('tr', false);
            $row->appendChildren(...$cells);
            $wrapper->appendChildren($row);
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->appendChildren($this->caption, $this->header, $this->body, $this->footer);
                $this->wrapped = true;
                return self::wrap($this)->build();
            }
            return parent::build();
        }
    }

}
