<?php

/**
 * Component class represent a HTML element. Can be used for creation and markup
 * generation that use a builder design pattern.
 * @author coder
 *
 * Created on: May 5, 2024 at 8:21:34 PM
 */

namespace gui\v1 {

    class Component implements \Stringable
    {

        private readonly string $htmlTag;
        private ?string $content = null;
        private array $children, $attributes, $classList, $inlineStyles = [];
        private array $inlineSourceCSS, $externalStyles = [];

        public function __construct(string $htmlTag, array $styleSource = [])
        {
            $this->htmlTag = $htmlTag;
            $this->inlineSourceCSS = $styleSource;
            $this->children = $this->classList = $this->attributes = [];
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->build();
        }

        /**
         * Generate component's HTML markups. This is the final method to be
         * called after creating a component.
         * @return string HTML string representing a component
         */
        public function build(): string
        {
            $css = $this->serializeStyles();
            if ($this->content !== null || !empty($this->children)) {
                $markup = '<' . $this->htmlTag . $css . $this->serializeAttributes() . '>';
                return $markup . $this->serializeChildren() . $this->content . '</' . $this->htmlTag . '>';
            }
            return '<' . $this->htmlTag . $css . $this->serializeAttributes() . '/>';
        }

        /**
         * Get the content of a component as HTML markups or texts
         * @return string|null
         */
        public function getContent(): ?string
        {
            return $this->content;
        }

        private function applyStyles(array &$styles, array $sourceStyles): self
        {
            foreach ($styles as $key => $value) {
                if ($value === null) {
                    $this->addClass($sourceStyles[$key]);
                    continue;
                }
                if (!empty($sourceStyles[$key][$value])) {
                    $this->addClass($sourceStyles[$key][$value]);
                }
            }
            return $this;
        }

        private function serializeStyles(): ?string
        {
            $this->applyStyles($this->externalStyles, Style::getStyles(array_keys($this->externalStyles)));
            $this->applyStyles($this->inlineStyles, $this->inlineSourceCSS);
            if (empty($this->classList)) {
                return null;
            }
            return ' class="' . trim(implode(' ', array_keys($this->classList))) . '"';
        }

        /**
         * Add a style to a component style collection
         * @param int $name Style name
         * @param \BackedEnum $value Style value
         * @return self
         * @see Component::setStyle()
         */
        protected function addStyle(int $name, \BackedEnum $value = null): self
        {
            $this->inlineStyles[$name] = $value?->value;
            return $this;
        }

        private function addExternalStyle(string $name, $value = null): self
        {
            $this->externalStyles[$name] = $value;
            return $this;
        }

        /**
         * Remove all CSS styles. It also remove all classes set by user. The
         * final result is unstyled component.
         * @return self
         */
        public function clearAllStyles(): self
        {
            return $this->clearClass()->clearExternalStyles()->clearDefaultStyles();
        }

        /**
         * Remove CSS default styles.
         * @return self
         */
        public function clearDefaultStyles(): self
        {
            $this->inlineStyles = [];
            return $this;
        }

        /**
         * Remove CSS style classes set by user.
         * @return self
         */
        public function clearClass(): self
        {
            $this->classList = [];
            return $this;
        }

        /**
         * Remove all CSS styles set using Style::*.
         * @return self
         */
        public function clearExternalStyles(): self
        {
            $this->externalStyles = [];
            return $this;
        }

        /**
         * Remove style from style collection
         * @param int $name Style name
         * @return self
         */
        protected function removeStyle(int $name): self
        {
            unset($this->inlineStyles[$name]);
            return $this;
        }

        /**
         * Create and return a copy of this component.
         * @return self A new copy of this component with all the features from
         * original component
         */
        public function copy(): self
        {
            return clone $this;
        }

        private function removeExternalStyle(string $name): self
        {
            unset($this->externalStyles[$name]);
            return $this;
        }

        /**
         * Set content as HTML markups or texts using file content.
         * @param string|null $filePath Path to a file to get content from
         * @return self
         */
        public function setContentFromFile(string $filePath): self
        {
            ob_start();
            require $filePath;
            $this->content = ob_get_clean();
            return $this;
        }

        /**
         * Set background color (and font color)
         * @param int|null $color Color values from Style::COLOR_*
         * @return self
         */
        public function setColor(?int $color): self
        {
            return $this->resetExternalStyle('colors', $color);
        }

        /**
         * Set position
         * @param int|null $position Position values from Style::POS_*
         * @return self
         */
        public function setPosition(?int $position): self
        {
            return $this->resetExternalStyle('positions', $position);
        }

        /**
         * Set alignment of a component horizontally or vertically
         * @param int|null $alignment Alignment value from Style::ALIGN_*
         * @return self
         */
        public function setAlignment(?int $alignment): self
        {
            return $this->resetExternalStyle('alignments', $alignment);
        }

        /**
         * Set gap to a component
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::GAP_*
         * @return self
         * @see Component::setSpacing()
         */
        public function setGap(?int $size, int $direction = null): self
        {
            $this->resetExternalStyle('gap_sizes', $size);
            return $this->resetExternalStyle('gaps', $direction);
        }

        /**
         * Set margin to
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::GAP_*
         * @return self
         * @see Component::setSpacing()
         */
        public function setMargin(?int $size, int $direction = null): self
        {
            $this->resetExternalStyle('margin_sizes', $size);
            return $this->resetExternalStyle('gaps', $direction);
        }

        /**
         * Set padding to a component
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::GAP_*
         * @return self
         * @see Component::setSpacing()
         */
        public function setPadding(?int $size, int $direction = null): self
        {
            $this->resetExternalStyle('padding_sizes', $size);
            return $this->resetExternalStyle('gaps', $direction);
        }

        /**
         * Set font size
         * @param int|null $size Size values from Style::SIZE_*
         * @return self
         */
        public function setFontSize(?int $size): self
        {
            return $this->resetExternalStyle('font_sizes', $size);
        }

        /**
         * Remove all borders
         * @return self
         */
        public function removeBorders(): self
        {
            return $this->removeExternalStyle('border');
        }

        /**
         * Set default borders
         * @return self
         */
        public function setBorders(): self
        {
            return $this->addExternalStyle('border');
        }

        /**
         * Whether a component can take parent's full height or not
         * @param bool $full
         * @return self
         */
        public function fullHeight(bool $full): self
        {
            if ($full) {
                return $this->addExternalStyle('full_height');
            }
            return $this->removeExternalStyle('full_height');
        }

        /**
         * Whether a component can take parent's full height or not
         * @param bool $full
         * @return self
         */
        public function fullWidh(bool $full): self
        {
            if ($full) {
                return $this->addExternalStyle('full_width');
            }
            return $this->removeExternalStyle('full_width');
        }

        /**
         * Set box shadow
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::SHADOW_*
         * @return self
         */
        public function setShadow(?int $size, int $direction = null): self
        {
            $this->resetExternalStyle('shadow_sizes', $size);
            return $this->resetExternalStyle('shadow_directions', $direction);
        }

        /**
         * Set round corners (border-radius)
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::POS_*
         * @return self
         */
        public function setCorners(?int $size, int $direction = null): self
        {
            $this->resetExternalStyle('corner_sizes', $size);
            return $this->resetExternalStyle('corner_radius', $direction);
        }

        private function resetExternalStyle(string $property, ?int $value): self
        {
            if ($value !== null) {
                return $this->addExternalStyle($property, $value);
            }
            return $this->removeExternalStyle($property);
        }

        /**
         * Set margin, padding and gap
         * @param int|null $size Size values from Style::SIZE_*
         * @return self
         */
        public function setSpacing(?int $size): self
        {
            return $this->setMargin($size)->setPadding($size)->setGap($size);
        }

        /**
         * Get child component by index
         * @param int $index Index of a child component
         * @return Component|null
         */
        public function getChild(int $index): ?Component
        {
            if ($index < 0) {
                return $this->children[$index] ?? null;
            }
            return $this->children[count($this->children) + $index] ?? null;
        }

        /**
         * Replacing a component child with the new one
         * @param int $oldChild Old component index
         * @param Component $newChild New component
         * @return self
         * @see Component::removeChild()
         */
        public function replaceChild(int $oldChild, Component $newChild): self
        {
            if (isset($this->children[$oldChild])) {
                $this->children[$oldChild] = $newChild;
            }
            return $this;
        }

        /**
         * Set parent component
         * @param Component $parent Parent component
         * @return self
         */
        public function setParent(Component &$parent): self
        {
            $parent->addExternalStyle('relative_position')->appendChildren($this);
            return $this;
        }

        /**
         * Move all element children of this component to a destination
         * @param Component $destination Destination component
         * @return self
         * @see Component::moveContent();
         */
        public function moveChildrenTo(Component &$destination): self
        {
            $destination->appendChildren(...$this->getChildren());
            return $this->removeChildren();
        }

        /**
         * Move content of this component to a destination
         * @param Component $destination Destination component
         * @return self
         * @see Component::moveChildren();
         */
        public function moveContentTo(Component &$destination): self
        {
            $destination->setContent($this->getContent());
            return $this->setContent(null);
        }

        /**
         * Check if style exists in component's style collection
         * @param int $name Style name
         * @param type $value Style value
         * @return bool True on success, false otherwise.
         */
        protected function hasStyle(int $name, $value = null): bool
        {
            if ($value === null) {
                return array_key_exists($name, $this->inlineStyles);
            }
            return array_key_exists($value, $this->inlineStyles[$name]);
        }

        /**
         * Set or unset a component active state
         * @param bool $active Value to set
         * @return self
         */
        public function setActive(bool $active): self
        {
            if ($active) {
                return $this->addExternalStyle('active');
            }
            return $this->removeExternalStyle('active');
        }
    }

}
