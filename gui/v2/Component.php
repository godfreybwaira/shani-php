<?php

/**
 * Description of Component
 * @author coder
 *
 * Created on: Mar 25, 2025 at 1:47:04 PM
 */

namespace gui\v2 {

    use gui\v2\decoration\Theme;

    abstract class Component implements \Stringable
    {

        private static array $styles = [];
        private readonly string $tag;
        private ?string $content = null;
        private ?Component $parent = null;
        private array $children, $attributes, $classList;
        private ?Theme $theme = null;

        protected function __construct(string $tag = 'div')
        {
            $this->tag = $tag;
            $this->children = $this->attributes = $this->classList = [];
        }

        /**
         * Add child(ren) component(s) at the beginning
         * @param Component $children Component(s) to add as child(ren)
         * @return self
         * @see Component::appendChildren()
         */
        public function prependChild(Component ...$children): self
        {
            $kids = array_map(fn(Component &$child) => $child->withParent($this), $children);
            array_unshift($this->children, ...$kids);
            return $this;
        }

        /**
         * Add one or more children components at the end
         * @param Component $children Component(s) to add as child(ren)
         * @return self
         * @see Component::appendChildren()
         */
        public function appendChild(Component ...$children): self
        {
            foreach ($children as &$child) {
                $this->children[] = $child->withParent($this);
            }
            return $this;
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
         * Remove all child elements of this component.
         * @return self
         */
        public function removeChildren(): self
        {
            $this->children = [];
            return $this;
        }

        /**
         * Get parent of a component. If a component has no parent, null is returned
         * @return self Parent component
         */
        public function getParent(): ?self
        {
            return $this->parent;
        }

        /**
         * Get a copy of this component with a new parent
         * @param Component $parent Parent component
         * @return self
         */
        private function withParent(Component &$parent): self
        {
            $this->parent = $parent;
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
         * Copy attributes from a component
         * @param Component $source Source component to copy attributes from
         * @return self
         */
        public function copyAttributes(Component $source): self
        {
            foreach ($source->attributes as $name => $value) {
                $this->setAttribute($name, $value);
            }
            return $this;
        }

        /**
         * Remove attribute(s) from a component
         * @param string $names Attribute(s) to remove
         * @return self
         */
        public function removeAttribute(string ...$names): self
        {
            foreach ($names as $value) {
                unset($this->attributes[$value]);
            }
            return $this;
        }

        /**
         * Toggle component attribute
         * @param string $name Attribute name
         * @param type $value Attribute value
         * @return self
         * @see Component::setAttribute()
         */
        public function toggleAttribute(string $name, $value = null): self
        {
            if ($this->hasAttribute($name)) {
                return $this->removeAttribute($name);
            }
            return $this->setAttribute($name, $value);
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
         * Add one or more children to a Component, removing all existing children
         * @param Component $children Component(s) to add as child(ren)
         * @return self
         * @see Component::appendChild()
         */
        public function setChild(Component ...$children): self
        {
            $this->children = [];
            return $this->appendChild(...$children);
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

        private function serializeAttributes(): ?string
        {
            if (!empty($this->classList)) {
                $this->setAttribute('class', implode(' ', $this->classList));
            }
            $result = null;
            foreach ($this->attributes as $name => $value) {
                $result .= ' ' . $name . ($value !== null ? '="' . $value . '"' : null);
            }
            return $result;
        }

        private function serializeChildren(): ?string
        {
            $result = null;
            foreach ($this->children as $child) {
                $result .= $child->build();
            }
            return $result;
        }

        /**
         * Add decorations (styles) to a Component
         * @return self
         */
        private function applyTheme(): self
        {
            if (!empty($this->theme)) {
                $name = $this->theme->getName();
                $this->classList[$name] = $this->theme->getId();
                self::$styles[$name] = $this->theme->getDecoration();
            }
            return $this;
        }

        /**
         * Get component HTML tag
         * @return string
         */
        public function getTag(): string
        {
            return $this->tag;
        }

        /**
         * Set text content, replacing other children but are not removed.
         * @param string|null $content Text content
         * @return self
         */
        public function setText(?string $content): self
        {
            $this->content = $content;
            return $this;
        }

        protected function setTheme(Theme $theme): self
        {
            $this->theme = $theme;
            return $this;
        }

        private function getStyles(): ?string
        {
            $markup = null;
            $this->applyTheme();
            if ($this->parent === null && !empty(self::$styles)) {
                $markup = '<style type="text/css">' . implode('', self::$styles) . '</style>';
                self::$styles = [];
            }
            return $markup;
        }

        /**
         * Generate HTML markups. This is the last method to call after creating
         * a component.
         * @return string HTML string representing a component
         */
        public function build(): string
        {
            $markup = $this->getStyles();
            if (!empty($this->content)) {
                $markup .= '<' . $this->tag . $this->serializeAttributes() . '>';
                return $markup . $this->content . '</' . $this->tag . '>';
            }
            if (!empty($this->children)) {
                $markup .= '<' . $this->tag . $this->serializeAttributes() . '>';
                return $markup . $this->serializeChildren() . '</' . $this->tag . '>';
            }
            return $markup . '<' . $this->tag . $this->serializeAttributes() . '/>';
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->build();
        }
    }

}
