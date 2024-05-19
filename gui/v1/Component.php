<?php

/**
 * Description of Component
 * @author coder
 *
 * Created on: May 5, 2024 at 8:21:34 PM
 */

namespace gui\v1 {

    class Component
    {

        private string $tag;
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

        public function __construct(string $tag, bool $gutters = true)
        {
            $this->tag = $tag;
            $this->children = $this->classList = $this->attributes = $this->props = [];
            $this->content = $this->gap = $this->fontSize = $this->padding = $this->shadow = $this->corner = null;
            if ($gutters) {
                $this->setGutters(self::SIZE_DEFAULT);
            }
        }

        public function __toString(): string
        {
            return $this->build();
        }

        public function build(): string
        {
            $css = $this->stringifyClass();
            if ($this->content !== null || !empty($this->children)) {
                $texts = '<' . $this->tag . $css . $this->stringifyAttr() . '>' . $this->content;
                return $texts . $this->stringifyChildren() . '</' . $this->tag . '>';
            }
            return '<' . $this->tag . $css . $this->stringifyAttr() . '/>';
        }

        public function addColumnSize(int $column, int $size): self
        {
            if ($column <= self::MAX_COLUMNS) {
                return $this->addProperty('width-' . self::SIZES[$size], $column);
            }
            throw new \InvalidArgumentException('Maximum column size is ' . self::MAX_COLUMNS);
        }

        public function fillHeight(): self
        {
            return $this->addProperty('height', 'fill');
        }

        public function fillWidth(): self
        {
            return $this->addProperty('width', 'fill');
        }

        public function hasClass(string $value): bool
        {
            return in_array($value, $this->classList);
        }

        public function removeClass(string ...$value): self
        {
            $this->classList = array_diff($this->classList, $value);
            return $this;
        }

        public function content(): ?string
        {
            return $this->content;
        }

        public function addClass(string ...$values): self
        {
            foreach ($values as $value) {
                if (!$this->hasClass($value)) {
                    $this->classList[] = $value;
                }
            }
            return $this;
        }

        public function hasAttribute(string $name): bool
        {
            return in_array($name, $this->attributes);
        }

        public function removeAttribute(string ...$names): self
        {
            foreach ($names as $value) {
                if (isset($this->attributes[$value])) {
                    unset($this->attributes[$value]);
                }
            }
            return $this;
        }

        public function setAttribute(string $name, $value = null): self
        {
            $this->attributes[$name] = $value;
            return $this;
        }

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

        public function getAttribute(string $name)
        {
            return $this->attributes[$name] ?? null;
        }

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

        public function setClass(string ...$values): self
        {
            $this->classList = [];
            return $this->addClass(...$values);
        }

        public function setProperty(string $name, $value = null): self
        {
            $this->props = [];
            return $this->addProperty($name, $value);
        }

        public function addProperty(string $name, $value = null): self
        {
            $this->props[$name] = $value;
            return $this;
        }

        public function clearStyles(): self
        {
            $this->props = [];
            $this->classList = [];
            return $this;
        }

        public function hasProperty(string $name): bool
        {
            return array_key_exists($name, $this->props);
        }

        public function removeProperty(string ...$names): self
        {
            foreach ($names as $key) {
                if (isset($this->props[$key])) {
                    unset($this->props[$key]);
                }
            }
            return $this;
        }

        public function setContent(?string $content): self
        {
            $this->content = $content;
            return $this;
        }

        protected function setColor(?int $color): self
        {
            if ($color !== null) {
                return $this->addProperty('color', self::COLORS[$color]);
            }
            return $this->removeProperty('color');
        }

        protected function setPosition(?int $position): self
        {
            if ($position !== null) {
                return $this->addProperty('pos', self::POSITIONS[$position]);
            }
            return $this->removeProperty('pos');
        }

        public function setAlign(?int $align): self
        {
            if ($align !== null) {
                return $this->addProperty('align', self::ALIGNS[$align]);
            }
            return $this->removeProperty('align');
        }

        public function setGap(?int $size, int $direction = null): self
        {
            return $this->initProp('gap', $size, $direction);
        }

        public function setMargin(?int $size, int $direction = null): self
        {
            return $this->initProp('margin', $size, $direction);
        }

        public function setPadding(?int $size, int $direction = null): self
        {
            return $this->initProp('padding', $size, $direction);
        }

        public function setFontSize(?int $size): self
        {
            return $this->initProp('font', $size);
        }

        public function setBorders(): self
        {
            return $this->addProperty('with-borders');
        }

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

        public function setCorners(?int $size, int $direction = null): self
        {
            return $this->initProp('corner', $size, $direction);
        }

        public function setGutters(?int $size): self
        {
            return $this->setMargin($size)->setPadding($size)->setGap($size);
        }

        public function toggleAttr(string $name, $value = null): self
        {
            if ($this->hasAttribute($name)) {
                return $this->removeAttribute($name);
            }
            return $this->setAttribute($name, $value);
        }

        public function toggleProperty(string $name, $value = null): self
        {
            if ($this->hasProperty($name)) {
                return $this->removeProperty($name);
            }
            return $this->addProperty($name, $value);
        }

        public function getChildren(): array
        {
            return $this->children;
        }

        public function getChild(int $index): ?Component
        {
            if ($index < 0) {
                return $this->children[$index] ?? null;
            }
            return $this->children[count($this->children) + $index] ?? null;
        }

        public function removeChild(int ...$index): self
        {
            foreach ($index as $value) {
                if (isset($this->children[$value])) {
                    unset($this->children[$value]);
                }
            }
            return $this;
        }

        public function replaceChild(int $oldChild, Component $newChild): self
        {
            if (isset($this->children[$oldChild])) {
                $this->children[$oldChild] = $newChild;
            }
            return $this;
        }

        public function setParent(Component &$parent): self
        {
            $parent->addProperty('relative-pos')->appendChildren($this);
            return $this;
        }

        public function setChildren(?Component ...$children): self
        {
            $this->children = [];
            if ($children !== null) {
                return $this->appendChildren(...$children);
            }
            return $this;
        }

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
