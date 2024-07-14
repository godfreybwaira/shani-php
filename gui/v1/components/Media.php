<?php

/**
 * Media represent any component with caption, example video or image component.
 * @author coder
 *
 * Created on: May 16, 2024 at 4:14:01 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Media extends Component
    {

        private const MEDIA = 0, MEDIA_CAPTION = 1;
        private const PROPS = [
            self::MEDIA => '',
            self::MEDIA_CAPTION => ''
        ];

        public function __construct(Component $media)
        {
            parent::__construct('div', self::PROPS);
            $this->addProperty(self::MEDIA);
            $this->appendChildren($media);
        }

        /**
         * Set media caption
         * @param Component $caption Media caption
         * @param int|null $position Position value from Style::POS_*
         * @return self
         */
        public function setCaption(Component $caption, ?int $position = null): self
        {
            $wrapper = new Component('div', self::PROPS);
            $wrapper->appendChildren($caption);
            $wrapper->addProperty(self::MEDIA_CAPTION);
            $wrapper->setPosition($position ?? Style::POS_BOTTOM);
            $this->appendChildren($wrapper);
            return $this;
        }
    }

}
