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
        private ?string $text, $markup, $gap, $fontSize, $padding, $shadow, $corner;

        protected const SIZES = ['sm', 'md', 'lg', 'xl'], COLORS = [ 'danger', 'success', 'alert', 'info', 'primary', 'secondary'];
        protected const POSITIONS = ['tl', 'tc', 'tr', 'cl', 'cc', 'cr', 'bl', 'bc', 'br', 'top', 'left', 'bottom', 'right'];
        public const SIZE_SM = 0, SIZE_MD = 1, SIZE_LG = 2, SIZE_XL = 3;
        public const COLOR_DANGER = 0, COLOR_SUCCESS = 1, COLOR_ALERT = 2, COLOR_INFO = 3, COLOR_PRIMARY = 4, COLOR_SECONDARY = 5;
        public const POS_TL = 0, POS_TC = 1, POS_TR = 2, POS_CL = 3, POS_CC = 4, POS_CR = 5, POS_BL = 6;
        public const POS_BC = 7, POS_BR = 8, POS_TOP = 9, POS_LEFT = 10, POS_BOTTOM = 11, POS_RIGHT = 12;
        public const SIZE_DEFAULT = self::SIZE_MD;

        public function __construct(string $tag, ?string $text = null, bool $gutters = true)
        {
            $this->tag = $tag;
            $this->text = $text;
            $this->children = $this->classList = $this->attributes = $this->props = [];
            $this->markup = $this->gap = $this->fontSize = $this->padding = $this->shadow = $this->corner = null;
            if ($gutters) {
                $this->setSize(self::SIZE_DEFAULT);
            }
        }

        public function __toString(): string
        {
            return $this->build();
        }

        public function build(): string
        {
            foreach ($this->props as $key => $value) {
                $this->setProps([$key . '-' . $value]);
            }
            $css = $this->stringifyClass();
            if ($this->text !== null || !empty($this->children) || $this->markup !== null) {
                $texts = '<' . $this->tag . $css . $this->stringifyAttr() . '>' . $this->text;
                return $texts . $this->markup . $this->stringifyChildren() . '</' . $this->tag . '>';
            }
            return '<' . $this->tag . $css . $this->stringifyAttr() . '/>';
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

        public function nthChild(int $index): ?Component
        {
            return $this->children[$index] ?? null;
        }

        public function text(): ?string
        {
            return $this->text;
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

        public function hasAttr(string $name): bool
        {
            return in_array($name, $this->attributes);
        }

        public function removeAttr(string $name): self
        {
            unset($this->attributes[$name]);
            return $this;
        }

        public function setAttr(string $name, $value = null): self
        {
            $this->attributes[$name] = $value;
            return $this;
        }

        public function attr(string $name)
        {
            return $this->attributes[$name] ?? null;
        }

        public function withoutAttr(): self
        {
            $copy = clone $this;
            $copy->attributes = [];
            return $copy;
        }

        public function withoutClasses(): self
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
                $result .= ' ' . $name . (empty($value) ? null : '="' . $value . '"');
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

        public function setProps(array $props): self
        {
            return $this->addClass(...Theme::styles(...$props));
        }

        public function setMarkup(string $markup): self
        {
            $this->markup = $markup;
            return $this;
        }

        protected function setColor(int $color): self
        {
            $this->props['color'] = self::COLORS[$color];
            return $this;
        }

        protected function setPosition(int $position): self
        {
            $this->props['pos'] = self::POSITIONS[$position];
            return $this;
        }

        public function setGap(?int $size): self
        {
            return $this->initProp('gap', $size);
        }

        public function setPadding(?int $size): self
        {
            return $this->initProp('padding', $size);
        }

        public function setFontSize(?int $size): self
        {
            return $this->initProp('font', $size);
        }

        public function setBorder(): self
        {
            return $this->setProps(['with-borders']);
        }

        public function setShadow(?int $size): self
        {
            return $this->initProp('shadow', $size);
        }

        private function initProp(string $prop, ?int $value): self
        {
            if ($value !== null) {
                $this->props[$prop] = self::SIZES[$value];
            } elseif (isset($this->props[$prop])) {
                unset($this->props[$prop]);
            }
            return $this;
        }

        public function setCorners(?int $size): self
        {
            return $this->initProp('corner', $size);
        }

        public function setSize(?int $size): self
        {
            return $this->setGap($size)->setPadding($size)->setFontSize($size);
        }

        public function toggleAttr(string $name, $value = null): self
        {
            if ($this->hasAttr($name)) {
                return $this->removeAttr($name);
            }
            return $this->setAttr($name, $value);
        }

        public function children(): array
        {
            return $this->children;
        }

        public function setParent(Component &$parent): self
        {
            $parent->setProps(['relative-pos'])->appendChildren($this);
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
