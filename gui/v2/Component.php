<?php

/**
 * Description of Component
 * @author coder
 *
 * Created on: Mar 25, 2025 at 1:47:04 PM
 */

namespace gui\v2 {


    class Component implements \Stringable
    {

        private array $children;
        private readonly string $tag;
        private ?string $content = null;
        private ?Component $parent = null;
        public readonly StyleClass $classList;
        public readonly Attribute $attribute;
        public readonly InlineStyle $style;

        public function __construct(string $tag = 'div')
        {
            $this->tag = $tag;
            $this->classList = new StyleClass();
            $this->attribute = new Attribute();
            $this->style = new InlineStyle();
            $this->children = [];
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

        private function serializeChildren(): ?string
        {
            $result = null;
            foreach ($this->children as $child) {
                $result .= $child->build();
            }
            return $result;
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

        /**
         * Generate HTML markups. This is the last method to call after creating
         * a component.
         * @return string HTML string representing a component
         */
        public function build(): string
        {
            if (!$this->classList->isEmpty()) {
                $this->attribute->addOne('class', $this->classList->asString());
            }
            if (!$this->style->isEmpty()) {
                $this->attribute->addOne('style', $this->style->asString());
            }
            $markup = null;
            if (!empty($this->content)) {
                $markup .= '<' . $this->tag . $this->attribute . '>';
                return $markup . $this->content . '</' . $this->tag . '>';
            }
            if (!empty($this->children)) {
                $markup .= '<' . $this->tag . $this->attribute . '>';
                return $markup . $this->serializeChildren() . '</' . $this->tag . '>';
            }
            return $markup . '<' . $this->tag . $this->attribute . '/>';
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->build();
        }
    }

}
