<?php

/**
 * Description of Lists
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Lists extends Component
    {

        private const NAME = 'list', TYPES = ['accordion'], STRIPES = ['even', 'odd'];
        public const TYPE_1 = 0, STRIPES_EVEN = 0, STRIPES_ODD = 1;

        private ?string $type = null, $stripes = null;

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function setAlign(bool $horizontal): self
        {
            if ($horizontal) {
                return $this->setProps([self::NAME . '-h']);
            }
            return $this;
        }

        public function setStripes(int $stripes): self
        {
            $this->stripes = self::STRIPES[$stripes];
            return $this;
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }

        public function addItem(ListItem ...$items): self
        {
            foreach ($items as $item) {
                $item->title()->setProps([self::NAME . '-item-title']);
            }
            $this->appendChildren(...$items);
        }

        public function build(): string
        {
            if ($this->stripes) {
                $this->setProps([self::NAME . '-' . $this->stripes]);
            }
            $this->setProps([self::NAME . '-type-' . $this->type]);
            return parent::build();
        }
    }

}
