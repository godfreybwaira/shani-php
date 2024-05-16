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

        private ?int $position = null;
        private Component $caption;

        public function __construct(Component $media)
        {
            parent::__construct('div', null, false);
            $this->setProps([self::NAME]);
            $this->appendChildren($media);
        }

        public function setCaption(Component $caption, ?int $position = null): self
        {
            $this->caption = $caption;
            $this->position = $position;
            return $this;
        }

        public function build(): string
        {
            if ($this->caption !== null) {
                $wrapper = new Component('div', null, false);
                $wrapper->appendChildren($this->caption);
                if ($this->position === null) {
                    $wrapper->setProps([self::NAME . '-caption-full']);
                } else {
                    $this->caption->setPosition($this->position);
                }
                $this->appendChildren($wrapper);
            }
            return parent::build();
        }
    }

}
