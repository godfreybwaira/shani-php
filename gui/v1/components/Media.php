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

        public function __construct(Component $media)
        {
            parent::__construct('div', false);
            $this->addProperty(self::NAME);
            $this->appendChildren($media);
        }

        public function setCaption(Component $caption, ?int $position = null): self
        {
            $pos = parent::POSITIONS[$position];
            $wrapper = new Component('div', false);
            $wrapper->appendChildren($caption);
            $wrapperName = self::NAME . '-caption';
            $wrapper->addProperty($wrapperName);
            $wrapper->addProperty($wrapperName . '-pos', $pos ?? 'full');
            $this->appendChildren($wrapper);
            return $this;
        }
    }

}
