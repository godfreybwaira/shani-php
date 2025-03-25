<?php

/**
 * Description of Widget
 * @author coder
 *
 * Created on: Mar 25, 2025 at 1:47:04 PM
 */

namespace gui\v1 {

    class Widget implements \Stringable
    {

        //WHAT ARE THE COMPONENTS OF A WIDGET?
        //attributes
        //classes
        //children
        //decoration
        private ?Widget $parent = null;

        public function __construct()
        {

        }

        /**
         * Set/Get a parent widget
         * @param Widget $parent Parent widget
         * @return self
         */
        public function parent(Widget &$parent = null): ?Widget
        {
            if ($parent === null) {
                return $this->parent ??= $parent;
            }
            return $this->parent;
        }

        /**
         * Remove a parent widget
         * @param Widget $parent Parent widget
         * @return self
         */
        public function removeParent(): self
        {

        }

        /**
         * Generate HTML markups. This is the final method to be called after
         * creating a widget.
         * @return string HTML string representing a widget
         */
        public function build(): string
        {

        }

        #[\Override]
        public function __toString(): string
        {
            return $this->build();
        }

        /**
         * Set or unset a widget active state
         * @param bool $active Value to set
         * @return self
         */
        public function setActive(bool $active): self
        {

        }

        /**
         * Return true if a widget is active when selected, activated etc.
         * @return bool
         */
        public function isActive(): bool
        {

        }

        /**
         * Create and return a copy of this widget.
         * @return self A new copy of this widget with all the features from
         * original widget
         */
        public function copy(): self
        {
            return clone $this;
        }
    }

}
