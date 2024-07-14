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
        private array $children, $attributes, $classList, $props, $propSource, $externalProps = [];

        public function __construct(string $htmlTag, ?array $styleSource = null)
        {
            $this->htmlTag = $htmlTag;
            $this->propSource = $styleSource ?? [];
            $this->children = $this->classList = $this->attributes = $this->props = [];
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
            $css = $this->stringifyClass();
            if ($this->content !== null || !empty($this->children)) {
                $texts = '<' . $this->htmlTag . $css . $this->stringifyAttr() . '>' . $this->content;
                return $texts . $this->stringifyChildren() . '</' . $this->htmlTag . '>';
            }
            return '<' . $this->htmlTag . $css . $this->stringifyAttr() . '/>';
        }

        /**
         * Check whether a component has a given CSS class.
         * @param string $value CSS class to check
         * @return bool
         */
        public function hasClass(string $value): bool
        {
            return in_array($value, $this->classList);
        }

        /**
         * Remove CSS class or list of classes
         * @param string $value CSS class(es) to remove
         * @return self
         */
        public function removeClass(string ...$value): self
        {
            $this->classList = array_diff($this->classList, $value);
            return $this;
        }

        /**
         * Get the content of a component as HTML markups or texts
         * @return string|null
         */
        public function content(): ?string
        {
            return $this->content;
        }

        /**
         * Add custom CSS class(es). It does not replace existing class(es)
         * @param string $values CSS class(es) to add
         * @return self
         * @see Component::setClass()
         */
        public function addClass(string ...$values): self
        {
            foreach ($values as $value) {
                if (!$this->hasClass($value)) {
                    $this->classList[] = $value;
                }
            }
            return $this;
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

        private function stringifyChildren(): ?string
        {
            $result = null;
            foreach ($this->children as $child) {
                $result .= $child->build();
            }
            return $result;
        }

        private function addStyleCollection(array $collection, array $source): self
        {
            foreach ($collection as $key => $value) {
                if ($value !== null) {
                    $this->addClass(...$source[$key][$value]);
                } else {
                    $this->addClass(...$source[$key]);
                }
            }
            return $this;
        }

        private function stringifyClass(): ?string
        {
            $this->addStyleCollection($this->props, $this->propSource);
            $this->addStyleCollection($this->externalProps, Style::getStyles(array_keys($this->externalProps)));
            if (empty($this->classList)) {
                return null;
            }
            return ' class="' . implode(' ', $this->classList) . '"';
        }

        private function stringifyAttr(): ?string
        {
            $result = null;
            foreach ($this->attributes as $name => $value) {
                $result .= ' ' . $name . ($value !== null ? '="' . $value . '"' : null);
            }
            return $result;
        }

        /**
         * Toggle CSS class(es)
         * @param string $classes CSS class(es) to toggle
         * @return self
         */
        public function toggleClass(string ...$classes): self
        {
            foreach ($classes as $css) {
                if ($this->hasClass($css)) {
                    $this->removeClass($css);
                } else {
                    $this->classList[] = $css;
                }
            }
            return $this;
        }

        /**
         * Add custom CSS class(es), removing all existing class(es)
         * @param string $values CSS class(es) to add
         * @return self
         * @see Component::addClass()
         */
        public function setClass(string ...$values): self
        {
            $this->classList = [];
            return $this->addClass(...$values);
        }

        /**
         * Set component property, removing all existing properties. A property
         * represent an array of CSS classes.
         * @param string $name Property name
         * @param type $value Property value
         * @return self
         * @see Component::addProperty()
         */
        public function setProperty(string $name, $value = null): self
        {
            $this->props = [];
            return $this->addProperty($name, $value);
        }

        /**
         * Set source to CSS class collection where properties will get their values from.
         * @param array $cssCollection CSS class collection
         * @return self
         */
        public function setStyleSource(array $cssCollection): self
        {
            $this->propSource = $cssCollection;
            return $this;
        }

        /**
         * Set component property. A property represent an array of CSS classes
         * @param int $id Property id
         * @param type $value Property value
         * @return self
         * @see Component::setProperty(), Component::setStyleSource()
         */
        public function addProperty(int $id, $value = null): self
        {
            $this->props[$id] = $value;
            return $this;
        }

        /**
         * Remove all CSS styles and properties
         * @return self
         */
        public function clearStyles(): self
        {
            $this->props = [];
            $this->classList = [];
            return $this;
        }

        /**
         * Check if a property exists
         * @param int $id Property id
         * @return bool
         */
        public function hasProperty(int $id): bool
        {
            return array_key_exists($id, $this->props);
        }

        /**
         * Remove a property
         * @param int $id Property id
         * @return self
         */
        public function removeProperty(int ...$id): self
        {
            foreach ($id as $key) {
                if (isset($this->props[$key])) {
                    unset($this->props[$key]);
                }
            }
            return $this;
        }

        /**
         * Set component content as HTML markups or texts
         * @param string|null $content HTML markups or text
         * @return self
         */
        public function setContent(?string $content): self
        {
            $this->content = $content;
            return $this;
        }

        /**
         * Set background color (and font color)
         * @param int|null $color Color values from Style::COLOR_*
         * @return self
         */
        public function setColor(?int $color): self
        {
            if ($color !== null) {
                return $this->addExternalProps('colors', $color);
            }
            return $this->removeExternalProps('colors');
        }

        /**
         * Set position
         * @param int|null $position Position values from Style::POS_*
         * @return self
         */
        public function setPosition(?int $position): self
        {
            if ($position !== null) {
                return $this->addExternalProps('positions', $position);
            }
            return $this->removeExternalProps('positions');
        }

        /**
         * Set alignment of a component horizontally or vertically
         * @param int|null $align Alignment value from Style::ALIGN_*
         * @return self
         */
        public function setAlign(?int $align): self
        {
            if ($align !== null) {
                return $this->addExternalProps('alignments', $align);
            }
            return $this->removeExternalProps('alignments');
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
            return $this->resetExternalProps('gap_sizes', 'gaps', $size, $direction);
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
            return $this->resetExternalProps('margin_sizes', 'gaps', $size, $direction);
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
            return $this->resetExternalProps('padding_sizes', 'gaps', $size, $direction);
        }

        /**
         * Set font size
         * @param int|null $size Size values from Style::SIZE_*
         * @return self
         */
        public function setFontSize(?int $size): self
        {
            if ($size !== null) {
                return $this->addExternalProps('font_sizes', $size);
            }
            return $this->removeExternalProps('font_sizes');
        }

        /**
         * Remove all borders
         * @return self
         */
        public function removeBorders(): self
        {
            return $this->removeExternalProps('border');
        }

        /**
         * Set default borders
         * @return self
         */
        public function setBorders(): self
        {
            return $this->addExternalProps('border');
        }

        /**
         * Whether a component can take parent's full height or not
         * @param bool $full
         * @return self
         */
        public function fullHeight(bool $full): self
        {
            if ($full) {
                return $this->addExternalProps('full_height');
            }
            return $this->removeExternalProps('full_height');
        }

        /**
         * Whether a component can take parent's full height or not
         * @param bool $full
         * @return self
         */
        public function fullWidh(bool $full): self
        {
            if ($full) {
                return $this->addExternalProps('full_width');
            }
            return $this->removeExternalProps('full_width');
        }

        /**
         * Set box shadow
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::SHADOW_*
         * @return self
         */
        public function setShadow(?int $size, int $direction = null): self
        {
            return $this->resetExternalProps('shadow_sizes', 'shadow_directions', $size, $direction);
        }

        /**
         * Set round corners (border-radius)
         * @param int|null $size Size values from Style::SIZE_*
         * @param int $direction Direction values from Style::DIRECTION_*
         * @return self
         */
        public function setCorners(?int $size, int $direction = null): self
        {
            return $this->resetExternalProps('corner_sizes', 'corner_directions', $size, $direction);
        }

        public function resetExternalProps(int $valueProp, int $dirProp, ?int $value, ?int $direction): self
        {
            if ($value !== null) {
                if ($direction !== null) {
                    $this->addExternalProps($dirProp, $direction);
                }
                return $this->addExternalProps($valueProp, $value);
            }
            return $this->removeExternalProps($valueProp);
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
         * Toggle property
         * @param string $name Property name
         * @param type $value Property value
         * @return self
         */
        public function toggleProperty(string $name, $value = null): self
        {
            if ($this->hasProperty($name)) {
                return $this->removeProperty($name);
            }
            return $this->addProperty($name, $value);
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
            $parent->addExternalProps('relative_position')->appendChildren($this);
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
         * Set or unset a component active state
         * @param bool $active Value to set
         * @return self
         */
        public function setActive(bool $active): self
        {
            if ($active) {
                return $this->addExternalProps('active');
            }
            return $this->removeExternalProps('active');
        }

        private function removeExternalProps(int $property): self
        {
            unset($this->externalProps[$property]);
            return $this;
        }

        private function addExternalProps(string $property, $value = null): self
        {
            $this->externalProps[$property] = $value;
            return $this;
        }
    }

}
