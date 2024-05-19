<?php

/**
 * Description of Media
 * @author coder
 *
 * Created on: May 16, 2024 at 4:14:01 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Media extends Component
    {

        private const NAME = 'media';

        private ?string $position = null;
        private Component $caption;

        public function __construct(Component $media)
        {
            parent::__construct('div', false);
            $this->addProperty(self::NAME);
            $this->appendChildren($media);
        }

        public function setCaption(Component $caption, ?int $position = null): self
        {
            $this->caption = $caption;
            $this->position = parent::POSITIONS[$position];
            return $this;
        }

        public function build(): string
        {
            if ($this->caption !== null) {
                $wrapper = new Component('div', false);
                $wrapper->appendChildren($this->caption);
                $wrapperName = self::NAME . '-caption';
                $wrapper->addProperty($wrapperName);
                $wrapper->addProperty($wrapperName . '-pos', $this->position ?? 'full');
                $this->appendChildren($wrapper);
            }
            return parent::build();
        }
    }

}
