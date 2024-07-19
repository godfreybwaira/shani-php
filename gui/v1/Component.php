<?php

/**
 * Component class represent a HTML element. Can be used for creation and markup
 * generation that use a builder design pattern.
 * @author coder
 *
 * Created on: May 5, 2024 at 8:21:34 PM
 */

namespace gui\v1 {

    class Component
    {

        private string $htmlTag;
        private ?string $content = null;
        private array $children, $attributes, $classList, $inlineStyles = [];
        private array $inlineSourceCSS, $externalStyles = [];

        public function __construct(string $htmlTag, array $styleSource = [])
        {
            $this->htmlTag = $htmlTag;
            $this->inlineSourceCSS = $styleSource;
            $this->children = $this->classList = $this->attributes = [];
        }

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
                $markup = '<' . $this->htmlTag . $css . $this->serializeAttributes() . '>' . $this->content;
                return $markup . $this->serializeChildren() . '</' . $this->htmlTag . '>';
            }
            return '<' . $this->htmlTag . $css . $this->serializeAttributes() . '/>';
        }

        /**
         * Check whether a component has a given CSS class.
         * @param string $css CSS class to check
         * @return bool
         */
        public function hasClass(string $css): bool
        {
            return array_key_exists($css, $this->classList);
        }

        /**
         * Remove CSS class or list of classes
         * @param string $classes CSS class(es) to remove separated by space
         * @return self
         */
        public function removeClass(string $classes): self
        {
            $values = explode(' ', trim($classes));
            foreach ($values as $css) {
                if ($this->hasClass($css)) {
                    unset($this->classList[$css]);
                }
            }
            return $this;
        }

        /**
         * Get the content of a component as HTML markups or texts
         * @return string|null
         */
        public function getContent(): ?string
        {
            return $this->content;
        }

        /**
         * Add custom CSS class(es). It does not replace existing class(es)
         * @param string $classes CSS class(es) to add separated by space
         * @return self
         * @see Component::setClass()
         */
        public function addClass(string $classes): self
        {
            $values = explode(' ', trim($classes));
            foreach ($values as $val) {
                $css = trim($val);
                if ($css !== '') {
                    $this->classList[$css] = null;
                }
            }
            return $this;
        }

        /**
         * Get all custom CSS classes defined by user
         * @return string CSS classes separated by space
         */
        public function getClass(): string
        {
            return implode(' ', array_keys($this->classList));
        }

        /**
         * Check if a given attribute exists in a component
         * @param string $name Attribute to check
         * @return bool True on success, false otherwise
         */
        public function hasAttribute(string $name): bool
        {
            return in_array($name, $this->attributes);
        }

        /**
         * Remove attribute(s) from a component
         * @param string $names Attribute(s) to remove
         * @return self
         */
        public function removeAttribute(string ...$names): self
        {
            foreach ($names as $value) {
                if (isset($this->attributes[$value])) {
                    unset($this->attributes[$value]);
                }
            }
            return $this;
        }

        /**
         * Set HTML attribute to a component
         * @param string $name Attribute name
         * @param type $value Attribute value. If not set the attribute value will
         * follow normal HTML rules for setting attribute
         * @return self
         * @see Component::copyAttributes()
         */
        public function setAttribute(string $name, $value = null): self
        {
            $this->attributes[$name] = $value;
            return $this;
        }

        /**
         * Copy attributes from source component and set to this component.
         * @param Component $source Source component to copy from
         * @param bool $skipDuplicates If set to true, it will skip all duplicate(s),
         * otherwise it will override current existing attribute(s)
         * @return self
         * @see Component::setAttribute()
         */
        public function copyAttributes(Component &$source, bool $skipDuplicates = true): self
        {
            $attrs = $source->getAttributes();
            foreach ($attrs as $name => $value) {
                if (!$skipDuplicates || !$this->hasAttribute($name)) {
                    $this->setAttribute($name, $value);
                }
            }
            return $this;
        }

        /**
         * Get single attribute value
         * @param string $name Attribute name
         * @return type Attribute value
         * @see Component::getAttributes()
         */
        public function getAttribute(string $name)
        {
            return $this->attributes[$name] ?? null;
        }

        /**
         * Get all attributes
         * @return array Component attribute(s)
         * @see Component::getAttribute()
         */
        public function getAttributes(): array
        {
            return $this->attributes;
        }

        private function serializeChildren(): ?string
        {
            $result = null;
            foreach ($this->children as $child) {
                $result .= $child->build();
            }
            return $result;
        }

        private function applyStyles(array &$styles, array &$sourceStyles): self
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

        private function serializeAttributes(): ?string
        {
            $result = null;
            foreach ($this->attributes as $name => $value) {
                $result .= ' ' . $name . ($value !== null ? '="' . $value . '"' : null);
            }
            return $result;
        }

        /**
         * Toggle CSS class(es)
         * @param string $classes CSS class(es) to toggle separated by space
         * @return self
         */
        public function toggleClass(string $classes): self
        {
            $values = explode(' ', trim($classes));
            foreach ($values as $css) {
                if ($this->hasClass($css)) {
                    $this->removeClass($css);
                } else {
                    $this->addClass($css);
                }
            }
            return $this;
        }

        /**
         * Add custom CSS class(es), removing all existing class(es)
         * @param string $classes CSS class(es) to add separated by space
         * @return self
         * @see Component::addClass()
         */
        public function setClass(string $classes): self
        {
            $this->classList = [];
            return $this->addClass($classes);
        }

        /**
         * Add a style to a component style collection
         * @param int $name Style name
         * @param type $value Style value
         * @return self
         * @see Component::setStyle()
         */
        protected function addStyle(int $name, $value = null): self
        {
            $this->inlineStyles[$name] = $value;
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
         * Set content as HTML markups or texts
         * @param string|null $content HTML markups or text content
         * @return self
         */
        public function setContent(?string $content): self
        {
            $this->content = $content;
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
         * Toggle component attribute
         * @param string $name Attribute name
         * @param type $value Attribute value
         * @return self
         * @see Component::setAttribute()
         */
        public function toggleAttr(string $name, $value = null): self
        {
            if ($this->hasAttribute($name)) {
                return $this->removeAttribute($name);
            }
            return $this->setAttribute($name, $value);
        }

        /**
         * Get all children components
         * @return array
         */
        public function getChildren(): array
        {
            return $this->children;
        }

        /**
         * Check if a component has one or more children. It does not include
         * text content or HTML markup
         * @return bool
         */
        public function hasChildren(): bool
        {
            return !empty($this->children);
        }

        public function hasContent(): bool
        {
            return !empty($this->children);
        }

        /**
         * Remove all child elements of this component.
         * @return self
         */
        public function removeChildren(): self
        {
            $this->children = [];
            return $this;
            ;
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
         * Remove child(ren) from Component
         * @param int $index Child(ren) index number to remove
         * @return self
         * @see Component::replaceChild()
         */
        public function removeChild(int ...$index): self
        {
            foreach ($index as $value) {
                if (isset($this->children[$value])) {
                    unset($this->children[$value]);
                }
            }
            return $this;
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
         * Add child(ren) component(s), removing all existing children
         * @param Component|null $children Component(s) to add as child(ren)
         * @return self
         * @see Component::appendChildren()
         */
        public function setChildren(?Component ...$children): self
        {
            $this->children = [];
            if ($children !== null) {
                return $this->appendChildren(...$children);
            }
            return $this;
        }

        /**
         * Add child(ren) component(s).
         * @param Component|null $children Component(s) to add as child(ren)
         * @return self
         * @see Component::setChildren()
         */
        public function appendChildren(?Component ...$children): self
        {
            foreach ($children as $child) {
                if ($child !== null) {
                    $this->children[] = $child;
                }
            }
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
