<?php

/**
 * Description of GridLayout
 * @author coder
 *
 * Created on: Jul 27, 2024 at 11:55:39 AM
 */

namespace gui\v2\containers {

    use gui\v2\Component;
    use gui\v2\props\TargetDevice;

    final class GridLayout extends Component
    {

        private array $gridAreas = [], $kids = [];

        private const CSS_CLASSNAME = 'grid-layout';

        /**
         * Create a responsive grid layout
         */
        public function __construct()
        {
            parent::__construct('div');
            $this->classList->addOne(self::CSS_CLASSNAME);
        }

        /**
         * Add a grid template area
         * @param TargetDevice $device Target device
         * @param GridArea $areas Grid area object(s)
         * @throws \RuntimeException Throws error if number of columns in a row
         * mismatch the previous row
         */
        public function addArea(TargetDevice $device, GridArea ...$areas): self
        {
            $totalRows = $this->gridAreas[$device->value][0] ?? 0;
            if ($totalRows > 0 && count($totalRows) !== count($areas)) {
                throw new \RuntimeException('Grid area mismatch the previous one');
            }
            $names = null;
            foreach ($areas as $area) {
                $name = $area->getUniqueName();
                $names .= ' ' . $name;
                $this->kids[$name] ??= $area;
            }
            $this->gridAreas[$device->value][] = '"' . ltrim($names) . '"';
            return $this;
        }

        public function build(): string
        {
            if (!empty($this->gridAreas)) {
                $css = null;
                $mobile = TargetDevice::MOBILE->value;
                foreach ($this->gridAreas as $device => $areas) {
                    $value = '.' . self::CSS_CLASSNAME . '{grid-template-areas:' . implode(null, $areas) . '}';
                    if ($device !== $mobile) {
                        $css .= '@media(min-width:' . $device . 'rem){' . $value . '}';
                    } else {
                        $css .= $value;
                    }
                }
                $style = new Component('style');
                $style->attribute->addOne('type', 'text/css');
                $style->setText($css);
                $this->appendChild($style);
            }
            $this->appendChild(...$this->kids);
            return parent::build();
        }
    }

}
/*
/////////////////////////////////
addArea($laptop,'header','header','header');
addArea($laptop,'left','main','main');
addArea($laptop,'left','main','main');
addArea($laptop,'left','footer','footer');
/////////////////////////////////
 */