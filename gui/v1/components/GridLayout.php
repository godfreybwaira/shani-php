<?php

/**
 * Description of GridLayout
 * @author coder
 *
 * Created on: Jul 27, 2024 at 11:55:39 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class GridLayout extends Component
    {

        private array $children, $queries;
        private string $id;

        private const GRID = 0;
        private const PROPS = [
            self::GRID => 'grid'
        ];

        /**
         * Create a responsive grid layout
         * @param string $id HTML id attribute
         */
        public function __construct(string $id = null)
        {
            parent::__construct('div', self::PROPS);
            $this->children = $this->queries = [];
            $this->id = $id ?? static::createId();
            $this->setAttribute('id', $this->id);
            $this->addStyle(self::GRID);
        }

        /**
         * Set device layout for responsiveness
         * @param int $device Target device set using TargetDevice::*
         * @param array $layout Two dimension array [][y] where [y] is the
         * associative array whose keys are the grid area names and values are
         * the integer number of columns does a cell should occupies
         * @throws \RuntimeException Throws error if number of columns in a row
         * mismatch the next row
         */
        public function setLayout(int $device, array $layout): self
        {
            $sum = array_sum($layout[0]);
            foreach ($layout as $row) {
                if (array_sum($row) !== $sum) {
                    throw new \RuntimeException('Column count mismatch the previous row');
                }
                $area = null;
                foreach ($row as $name => $span) {
                    $area .= str_repeat(' ' . $name, $span);
                    if (empty($this->children[$name])) {
                        $child = new Component('div');
                        $child->setAttribute('style', 'grid-area:' . $name);
                        $this->children[$name] = $child;
                    }
                }
                $this->queries[$device] .= '"' . ltrim($area) . '"';
            }
            return $this;
        }

        /**
         * Get a grid cell object
         * @param string $gridName Grid area name
         * @return Component Grid cell object
         */
        public function getCell(string $gridName): Component
        {
            return $this->children[$gridName];
        }

        public function build(): string
        {
            if (!empty($this->queries)) {
                $css = null;
                $mobile = \gui\v1\TargetDevice::MOBILE;
                foreach ($this->queries as $device => $query) {
                    if ($device === $mobile) {
                        $css .= '#' . $this->id . '{grid-template-areas:' . $query . '}';
                    } else {
                        $css .= '@media(min-width:' . $device . 'rem){';
                        $css .= '#' . $this->id . '{grid-template-areas:' . $query . '}}';
                    }
                }
                $style = new Component('style');
                $style->setAttribute('type', 'text/css')->setContent($css);
                $this->appendChildren($style);
            }
            $this->appendChildren(...$this->children);
            return parent::build();
        }
    }

}
