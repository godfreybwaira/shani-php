<?php

/**
 * Description of Layout
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Layout extends Component
    {

        private const NAME = 'layout';

        private array $rows = [];

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function addRows(int $rows, int $columns = 1): self
        {
            $i = count($this->rows);
            for ($r = 0; $r < $rows; $r++) {
                $this->rows[$r + $i] = new Component('li', false);
                for ($c = 0; $c < $columns; $c++) {
                    $this->rows[$r + $i]->appendChildren(new Component('div', false));
                }
            }
            return $this->appendChildren(...$this->rows);
        }

        public function &getRow(int $index): ?Component
        {
            return $this->rows[$index] ?? null;
        }
    }

}
