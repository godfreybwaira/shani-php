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
                $this->setGutter(self::SIZE_DEFAULT);
            }
        }

        public function __toString(): string
        {
            return $this->build();
        }

        public function build(): string
        {
            foreach ($this->props as $key => $value) {
                if ($value !== null) {
                    $this->addClass(...Theme::styles($key . '-' . $value));
                } else {
                    $this->addClass(...Theme::styles($key));
                }
            }
            $css = $this->stringifyClass();
            if ($this->content !== null || !empty($this->children)) {
                $texts = '<' . $this->tag . $css . $this->stringifyAttr() . '>' . $this->content;
                return $texts . $this->stringifyChildren() . '</' . $this->tag . '>';
            }
            return '<' . $this->tag . $css . $this->stringifyAttr() . '/>';
        }

        public function setColumnSize(int $column, int $size): Component
        {
            if ($column <= self::MAX_COLUMNS) {
                return $this->addProperty('width', self::SIZES[$size] . '-' . $column);
            }
            throw new \InvalidArgumentException('Maximum column size is ' . self::MAX_COLUMNS);
        }

        public function fillHeight(): Component
        {
            return $this->addProperty('height', 'fill');
        }

        public function fillWidth(): Component
        {
            return $this->addProperty('width', 'fill');
        }

        public function hasClass(string $value): bool
        {
            return in_array($value, $this->classList);
        }

        public function removeClass(string ...$value): Component
        {
            $this->classList = array_diff($this->classList, $value);
            return $this;
        }

        public function content(): ?string
        {
            return $this->content;
        }

        public function addClass(string ...$values): Component
        {
            foreach ($values as $value) {
                if (!$this->hasClass($value)) {
                    $this->classList[] = $value;
                }
            }
            return $this;
        }

        public function hasAttr(string $name): bool
        {
            return in_array($name, $this->attributes);
        }

        public function removeAttr(string $name): Component
        {
            unset($this->attributes[$name]);
            return $this;
        }

        public function setAttr(string $name, $value = null): Component
        {
            $this->attributes[$name] = $value;
            return $this;
        }

        public function getAttr(string $name)
        {
            return $this->attributes[$name] ?? null;
        }

        public function withoutAttr(): Component
        {
            $copy = clone $this;
            $copy->attributes = [];
            return $copy;
        }

        public function withoutClasses(): Component
        {
            $copy = clone $this;
            $copy->classList = [];
            return $copy;
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

        public function toggleClass(string ...$classes): Component
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

        public function setClass(string ...$values): Component
        {
            $this->classList = [];
            return $this->addClass(...$values);
        }

        public function addProperty(string $key, ?string $value = null): Component
        {
            $this->props[$key] = $value;
            return $this;
        }

        public function removeProperty(string ...$keys): Component
        {
            foreach ($keys as $key) {
                unset($this->props[$key]);
            }
            return $this;
        }

        public function setContent(?string $content): Component
        {
            $this->content = $content;
            return $this;
        }

        protected function setColor(?int $color): Component
        {
            if ($color !== null) {
                return $this->addProperty('color', self::COLORS[$color]);
            }
            return $this->removeProperty('color');
        }

        protected function setPosition(?int $position): Component
        {
            if ($position !== null) {
                return $this->addProperty('pos', self::POSITIONS[$position]);
            }
            return $this->removeProperty('pos');
        }

        public function setAlign(?int $align): Component
        {
            if ($align !== null) {
                return $this->addProperty('align', self::ALIGNS[$align]);
            }
            return $this->removeProperty('align');
        }

        public function setGap(?int $size, int $direction = null): Component
        {
            return $this->initProp('gap', $size, $direction);
        }

        public function setMargin(?int $size, int $direction = null): Component
        {
            return $this->initProp('margin', $size, $direction);
        }

        public function setPadding(?int $size, int $direction = null): Component
        {
            return $this->initProp('padding', $size, $direction);
        }

        public function setFontSize(?int $size): Component
        {
            return $this->initProp('font', $size);
        }

        public function setBorder(): Component
        {
            return $this->addProperty('with-borders');
        }

        public function setShadow(?int $size, int $direction = null): Component
        {
            return $this->initProp('shadow', $size, $direction);
        }

        private function initProp(string $prop, ?int $value, ?int $direction = null): Component
        {
            if ($value !== null) {
                $dir = $direction !== null ? '-' . self::DIRECTIONS[$direction] : null;
                return $this->addProperty($prop, $dir . self::SIZES[$value]);
            }
            return $this->removeProperty($prop);
        }

        public function setCorners(?int $size, int $direction = null): Component
        {
            return $this->initProp('corner', $size, $direction);
        }

        public function setGutter(?int $size): Component
        {
            return $this->setMargin($size)->setPadding($size)->setGap($size);
        }

        public function toggleAttr(string $name, $value = null): Component
        {
            if ($this->hasAttr($name)) {
                return $this->removeAttr($name);
            }
            return $this->setAttr($name, $value);
        }

        public function getChild(int $index): ?Component
        {
            return $this->children[$index] ?? null;
        }

        public function removeChild(int $index): Component
        {
            if (isset($this->children[$index])) {
                unset($this->children[$index]);
            }
            return $this;
        }

        public function replaceChild(int $oldChild, Component $newChild): Component
        {
            if (isset($this->children[$oldChild])) {
                $this->children[$oldChild] = $newChild;
            }
            return $this;
        }

        public function setParent(Component &$parent): Component
        {
            $parent->addProperty('relative-pos')->appendChildren($this);
            return $this;
        }

        public function setChildren(?Component ...$children): Component
        {
            $this->children = [];
            if ($children !== null) {
                return $this->appendChildren(...$children);
            }
            return $this;
        }

        public function appendChildren(?Component ...$children): Component
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
