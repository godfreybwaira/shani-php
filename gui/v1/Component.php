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
        private array $children, $attributes, $classList;
        private ?string $text, $markup, $gap, $fontSize, $padding, $shadow;

        protected const SIZES = ['sm', 'md', 'lg', 'xl'], COLORS = [ 'danger', 'success', 'alert', 'info'];
        protected const POSITIONS = ['pos-tl', 'pos-tc', 'pos-tr', 'pos-cl', 'pos-cc', 'pos-cr', 'pos-bl', 'pos-bc', 'pos-br'];
        public const SIZE_SM = 0, SIZE_MD = 1, SIZE_LG = 2, SIZE_XL = 3;
        public const COLOR_DANGER = 0, COLOR_SUCCESS = 1, COLOR_ALERT = 2, COLOR_INFO = 3;
        public const POS_TL = 0, POS_TC = 1, POS_TR = 2, POS_CL = 3, POS_CC = 4, POS_CR = 5, POS_BL = 6, POS_BC = 7, POS_BR = 8;

        public function __construct(string $tag, ?string $text = null)
        {
            $this->tag = $tag;
            $this->text = $text;
            $this->children = $this->classList = $this->attributes = [];
            $this->markup = $this->gap = $this->fontSize = $this->padding = $this->shadow = null;
        }

        public function __toString(): string
        {
            return $this->build();
        }

        public function build(): string
        {
            if ($this->gap !== null) {
                $this->setProps(['gap-' . $this->gap]);
            }
            if ($this->fontSize !== null) {
                $this->setProps(['font-' . $this->fontSize]);
            }
            if ($this->padding !== null) {
                $this->setProps(['padding-' . $this->padding]);
            }
            if ($this->shadow !== null) {
                $this->setProps(['shadow-' . $this->shadow]);
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

        public function withoutChildren(): self
        {
            $copy = clone $this;
            $copy->children = [];
            return $copy;
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

        public function setGap(int $size): self
        {
            $this->gap = self::SIZES[$size];
            return $this;
        }

        public function setPadding(int $size): self
        {
            $this->padding = self::SIZES[$size];
            return $this;
        }

        public function setFontSize(int $size): self
        {
            $this->fontSize = self::SIZES[$size];
            return $this;
        }

        public function setBorder(): self
        {
            return $this->setProps(['with-borders']);
        }

        public function setShadow(int $size): self
        {
            $this->shadow = self::SIZES[$size];
            return $this;
        }

        public function setSize(int $size): self
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

        public function setChildren(?Component ...$children): self
        {
            $this->children = [];
            return $this->appendChildren(...$children);
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
