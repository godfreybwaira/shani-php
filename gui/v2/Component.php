<?php

/**
 * Description of Component
 * @author coder
 *
 * Created on: Mar 25, 2025 at 1:47:04 PM
 */

namespace gui\v2 {

    use gui\v2\decorators\Animation;
    use gui\v2\decorators\Color;
    use gui\v2\decorators\Stripes;
    use gui\v2\props\Attribute;
    use gui\v2\props\DeviceSize;
    use gui\v2\props\InlineStyle;
    use gui\v2\props\StyleClass;

    class Component implements \Stringable
    {

        private array $children;
        private readonly string $tag;
        private ?string $content = null;
        private ?Animation $animation = null;
        private ?Color $color = null;
        public readonly StyleClass $classList;
        public readonly Attribute $attribute;
        public readonly InlineStyle $style;

        /**
         * Create a representation of HTML component
         * @param string $tag HTML tag
         */
        public function __construct(string $tag = 'div')
        {
            $this->tag = $tag;
            $this->classList = new StyleClass();
            $this->attribute = new Attribute();
            $this->style = new InlineStyle();
            $this->children = [];
            $this->attribute->addOne('id', 'd' . substr(hrtime(true), 8));
        }

        /**
         * Set informative color
         * @param Color $color color to set
         * @return self
         */
        public function setInformativeColor(Color $color): self
        {
            $this->color = $color;
            return $this;
        }

        /**
         * Set list stripes
         * @param Stripes $stripe
         * @return self
         */
        public function setStripes(Stripes $stripe): self
        {
            $this->classList->addOne($stripe->value);
        }

        /**
         * Set different size based on the device width
         * @param DeviceSize $device Device size
         * @param int|null $width Size from 1 to 12
         * @param int|null $height Size from 1 to 12
         * @return self
         */
        public function addDimension(DeviceSize $device, ?int $width, ?int $height): self
        {
            if ($width !== null) {
                $this->classList->addOne('width-' . $device->value . '-' . $width);
            }
            if ($height !== null) {
                $this->classList->addOne('height-' . $device->value . '-' . $height);
            }
            return $this;
        }

        /**
         * Get component unique id
         * @return string
         */
        public function getId(): string
        {
            return $this->attribute->getOne('id');
        }

        /**
         * Add child(ren) component(s) at the beginning
         * @param Component $children Component(s) to add as child(ren)
         * @return self
         * @see Component::appendChildren()
         */
        public function prependChild(Component ...$children): self
        {
            array_unshift($this->children, ...$children);
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
                $this->children[] = $child;
            }
            return $this;
        }

        /**
         * Get all children components
         * @return \Generator
         */
        public function getChildren(): \Generator
        {
            foreach ($this->children as $key => $child) {
                yield $key => $child;
            }
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
         * Remove child element from this component.
         * @param callable $cb A callback function with signature <code>$cb(Component $child):bool</code>
         * @return self
         */
        public function removeChild(callable $cb): self
        {
            foreach ($this->children as $idx => $kid) {
                if ($cb($kid)) {
                    unset($this->children[$idx]);
                }
            }
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
            if (count($this->children) === 1) {
                return $this->children[0]->open();
            }
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
            return $this->open() . $this->close();
        }

        /**
         * Set animation
         * @param Animation $animation Animation object
         * @return self
         */
        public function setAnimation(Animation $animation): self
        {
            $this->animation = $animation;
            return $this;
        }

        private static $counter = 0;
        private bool $opened = false;

        /**
         * Start outputting component as HTML
         * @return string HTML open tag and content (including children)
         * @see self::close()
         */
        public function open(): string
        {
            if ($this->opened) {
                return '';
            }
            $this->opened = true;
            if ($this->color !== null) {
                $this->classList->addOne($this->color->value);
            }
            if ($this->animation !== null) {
                $this->classList->addOne($this->animation->value);
            }
            if (!$this->classList->isEmpty()) {
                $this->attribute->addOne('class', $this->classList->asString());
            }
            if (!$this->style->isEmpty()) {
                $this->attribute->addOne('style', $this->style->asString());
            }
            if (!empty($this->content)) {
                return '<' . $this->tag . $this->attribute . '>' . $this->content;
            } elseif (!empty($this->children)) {
                return '<' . $this->tag . $this->attribute . '>' . $this->serializeChildren();
            }
            return '<' . $this->tag . $this->attribute . '>';
        }

        /**
         * Return HTML closing tag
         * @return string Return the HTML closing tag.
         */
        public function close(): string
        {
            $html = '';
            if (!$this->opened) {
                return $html;
            }
            $this->opened = false;
            foreach ($this->children as $child) {
                $html .= $child->close();
            }
            return $html . '</' . $this->tag . '>';
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->build();
        }
    }

}
