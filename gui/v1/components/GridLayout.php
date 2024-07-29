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

        private array $children, $areas, $queries;
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
            $this->children = $this->areas = $this->queries = [];
            $this->id = $id ?? 'id' . hrtime(true);
            $this->setAttribute('id', $this->id);
            $this->addStyle(self::GRID);
        }

        /**
         * Set device layout for responsiveness
         * @param int $device Target device set using TargetDevice::DEVICE_*
         * @param array $layout Two dimension array [][y] where [y] represents
         * associative array whose keys is the grid area name and value is the
         * integer that explain how much columns does a cell should occupies
         * @return self
         */
        public function setLayout(int $device, array $layout): self
        {
            foreach ($layout as $row) {
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
         * @return Component|null Grid cell object or null if object not found
         */
        public function getCell(string $gridName): ?Component
        {
            return $this->children[$gridName] ?? null;
        }

        public function build(): string
        {
            if (!empty($this->queries)) {
                $css = null;
                $mobile = \gui\v1\TargetDevice::DEVICE_MOBILE;
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
