<?php

/**
 * Description of Media
 * @author coder
 *
 * Created on: May 27, 2025 at 9:42:39 AM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\Position;

    final class Media extends Component
    {

        private const CSS_CLASS = 'media';

        private ?Component $caption = null, $media = null;
        private readonly Position $position;

        public function __construct(Position $captionPosition = Position::BOTTOM)
        {
            parent::__construct('div');
            $this->position = $captionPosition;
        }

        /**
         * Set media (img, picture, audio or video)
         * @param Component $media Media created using img, picture, audio or video tag
         * @return self
         */
        public function setMedia(Component $media): self
        {
            $this->media = $media;
            return $this;
        }

        public function getCaption(): Component
        {
            if ($this->caption === null) {
                $this->caption = new Component();
                $this->caption->classList->addAll([$this->position->value, self::CSS_CLASS . '-caption']);
                $this->appendChild($this->caption);
            }
            return $this->caption;
        }

        public function open(): string
        {
            $this->appendChild($this->media);
            return parent::open();
        }
    }

}
