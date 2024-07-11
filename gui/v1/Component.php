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
        private array $children, $attributes, $classList, $props;
        private ?string $content, $gap, $fontSize, $padding, $shadow, $corner;

        private const MAX_COLUMNS = 24;
        protected const SIZES = ['sm', 'md', 'lg', 'xl', 'full'];
        protected const COLORS = ['danger', 'success', 'alert', 'info', 'primary', 'secondary', 'transluscent'];
        protected const POSITIONS = ['tl', 'tc', 'tr', 'cl', 'cc', 'cr', 'bl', 'bc', 'br', 'top', 'left', 'bottom', 'right'];
        protected const DIRECTIONS = ['x', 'y', 'top', 'bottom'], ALIGNS = [ 'x', 'y'];
        public const SIZE_SM = 0, SIZE_MD = 1, SIZE_LG = 2, SIZE_XL = 3, SIZE_FULL = 4;
        public const COLOR_DANGER = 0, COLOR_SUCCESS = 1, COLOR_ALERT = 2, COLOR_INFO = 3;
        public const COLOR_PRIMARY = 4, COLOR_SECONDARY = 5, COLOR_TRANSLUSCENT = 6;
        public const POS_TL = 0, POS_TC = 1, POS_TR = 2, POS_CL = 3, POS_CC = 4, POS_CR = 5, POS_BL = 6;
        public const POS_BC = 7, POS_BR = 8, POS_TOP = 9, POS_LEFT = 10, POS_BOTTOM = 11, POS_RIGHT = 12;
        public const DIRECTION_X = 0, DIRECTION_Y = 1, DIRECTION_TOP = 2, DIRECTION_BOTTOM = 3;
        public const SIZE_DEFAULT = self::SIZE_MD, ALIGN_X = 0, ALIGN_Y = 1;

        public function __construct(string $htmlTag, bool $spacing = true)
        {
            $this->htmlTag = $htmlTag;
            $this->children = $this->classList = $this->attributes = $this->props = [];
            $this->content = $this->gap = $this->fontSize = $this->padding = $this->shadow = $this->corner = null;
            if ($spacing) {
                $this->setSpacing(self::SIZE_DEFAULT);
            }
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

        public function addColumnSize(int $column, int $size): self
        {
            if ($column <= self::MAX_COLUMNS) {
                return $this->addProperty('width-' . self::SIZES[$size], $column);
            }
            throw new \InvalidArgumentException('Maximum column size is ' . self::MAX_COLUMNS);
        }

        /**
         * Set height of a component to fit height of a parent component
         * @return self
         */
        public function fillHeight(): self
        {
            return $this->addProperty('height', 'fill');
        }

        /**
         * Set width of a component to fit width of a parent component
         * @return self
         */
        public function fillWidth(): self
        {
            return $this->addProperty('width', 'fill');
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
         * Remove CSS class or list of classes to a component
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
         * Add custom CSS class(es) to a component. Does not replace existing class(es)
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
         * Get single component attribute value
         * @param string $name Attribute name to get
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

        private function stringifyClass(): ?string
        {
            foreach ($this->props as $key => $value) {
                if ($value !== null) {
                    $this->addClass(...Theme::styles($key . '-' . $value));
                } else {
                    $this->addClass(...Theme::styles($key));
                }
            }
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
         * Toggle attribute's CSS class(es)
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
         * Add custom CSS class(es) to a component, removing all existing class(es)
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
         * Set component property, removing all existing properties
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
         * Set component property
         * @param string $name Property name
         * @param type $value Property value
         * @return self
         * @see Component::setProperty()
         */
        public function addProperty(string $name, $value = null): self
        {
            $this->props[$name] = $value;
            return $this;
        }

        /**
         * Remove all CSS styles
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
         * @param string $name Property name
         * @return bool
         */
        public function hasProperty(string $name): bool
        {
            return array_key_exists($name, $this->props);
        }

        /**
         * Remove property
         * @param string $names Property name
         * @return self
         */
        public function removeProperty(string ...$names): self
        {
            foreach ($names as $key) {
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
         * @param int|null $color Color values from Component::COLORS
         * @return self
         */
        protected function setColor(?int $color): self
        {
            if ($color !== null) {
                return $this->addProperty('color', self::COLORS[$color]);
            }
            return $this->removeProperty('color');
        }

        /**
         * Set position
         * @param int|null $position Position values from Component::POSITIONS
         * @return self
         */
        protected function setPosition(?int $position): self
        {
            if ($position !== null) {
                return $this->addProperty('pos', self::POSITIONS[$position]);
            }
            return $this->removeProperty('pos');
        }

        /**
         * Set alignment of a component horizontally or vertically
         * @param int|null $align Alignment value from Component::ALIGNS
         * @return self
         */
        public function setAlign(?int $align): self
        {
            if ($align !== null) {
                return $this->addProperty('align', self::ALIGNS[$align]);
            }
            return $this->removeProperty('align');
        }

        /**
         * Set gap to a component
         * @param int|null $size Size values from Component::SIZES
         * @param int $direction Direction values from Component::DIRECTIONS
         * @return self
         * @see Component::setSpacing()
         */
        public function setGap(?int $size, int $direction = null): self
        {
            return $this->initProp('gap', $size, $direction);
        }

        /**
         * Set margin to a component
         * @param int|null $size Size values from Component::SIZES
         * @param int $direction Direction values from Component::DIRECTIONS
         * @return self
         * @see Component::setSpacing()
         */
        public function setMargin(?int $size, int $direction = null): self
        {
            return $this->initProp('margin', $size, $direction);
        }

        /**
         * Set padding to a component
         * @param int|null $size Size values from Component::SIZES
         * @param int $direction Direction values from Component::DIRECTIONS
         * @return self
         * @see Component::setSpacing()
         */
        public function setPadding(?int $size, int $direction = null): self
        {
            return $this->initProp('padding', $size, $direction);
        }

        /**
         * Set font size
         * @param int|null $size Size values from Component::SIZES
         * @return self
         */
        public function setFontSize(?int $size): self
        {
            return $this->initProp('font', $size);
        }

        /**
         * Remove all borders
         * @return self
         */
        public function removeBorders(): self
        {
            return $this->removeProperty('with-borders');
        }

        /**
         * Set default borders
         * @return self
         */
        public function setBorders(): self
        {
            return $this->addProperty('with-borders');
        }

        /**
         * Set box shadow
         * @param int|null $size Size values from Component::SIZES
         * @param int $direction Direction values from Component::DIRECTIONS
         * @return self
         */
        public function setShadow(?int $size, int $direction = null): self
        {
            return $this->initProp('shadow', $size, $direction);
        }

        private function initProp(string $prop, ?int $value, ?int $direction = null): self
        {
            if ($value !== null) {
                $dir = $direction !== null ? '-' . self::DIRECTIONS[$direction] : null;
                return $this->addProperty($prop, $dir . self::SIZES[$value]);
            }
            return $this->removeProperty($prop);
        }

        /**
         * Set round corners (border-radius)
         * @param int|null $size Size values from Component::SIZES
         * @param int $direction Direction values from Component::DIRECTIONS
         * @return self
         */
        public function setCorners(?int $size, int $direction = null): self
        {
            return $this->initProp('corner', $size, $direction);
        }

        /**
         * Set margin, padding and gap
         * @param int|null $size Size values from Component::SIZES
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
            $parent->addProperty('relative-pos')->appendChildren($this);
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
    }

}
