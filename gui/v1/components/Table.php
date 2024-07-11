<?php

/**
 * Table is a structured set of data made up of rows and columns, used to organize
 * and display information in a grid format.
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

        private const NAME = 'table';

        public function __construct(string $caption = null)
        {
            parent::__construct('table');
            $this->header = $this->body = $this->footer = null;
            $this->setCaption($caption)->addProperty(self::NAME);
        }

        private static function wrap(Table $table): Component
        {
            $wrapper = new Component('div', false);
            $wrapper->addProperty(self::NAME . '-wrapper');
            return $wrapper->appendChildren($table);
        }

        /**
         * Set table caption
         * @param string|null $caption Table caption
         * @return self
         */
        public function setCaption(?string $caption): self
        {
            if (!$this->caption && $caption !== null) {
                $this->caption = new Component('caption', false);
                $this->caption->setContent($caption);
            }
            return $this;
        }

        /**
         * Create a table row and add some cells
         * @param Component $td Row cells
         * @return self
         */
        public function setRow(Component ...$td): self
        {
            return $this->setData('tbody', $this->body, ...$td);
        }

        /**
         * Create table header
         * @param Component $th Table header cell
         * @return self
         */
        public function setHeader(Component ...$th): self
        {
            return $this->setData('thead', $this->header, ...$th);
        }

        /**
         * Create table footer and add some cells
         * @param Component $td Table footer cell(s)
         * @return self
         */
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
