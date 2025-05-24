<?php

/**
 * Description of Card
 * @author coder
 *
 * Created on: May 24, 2025 at 11:06:35 PM
 */

namespace gui\v2\containers {

    use gui\v2\Component;
    use gui\v2\decorators\Direction;
    use gui\v2\decorators\Size;

    final class Card extends Component
    {

        public function __construct(Size $size = Size::MEDIUM, Direction $dir = null)
        {
            parent::__construct('div');
            $this->classList->addAll([$size->value, 'shadow-sm']);
            if ($dir !== null) {
                $this->classList->addOne($dir->value);
            }
        }

        /**
         * Set a card image
         * @param Component $image Card image
         * @return self
         * @throws \Exception Throw exception if image is not a valid image
         */
        public function setImage(Component $image): self
        {
            if ($image->getTag() !== 'img') {
                throw new \Exception('Invalid card image');
            }
            $this->appendChild($image);
            return $this;
        }

        /**
         * Set a card title
         * @param Component $title
         * @return self
         */
        public function setTitle(Component $title): self
        {
            $title->classList->addOne('card-title');
            $this->appendChild($title);
            return $this;
        }

        /**
         * Set a card body
         * @param Component $body
         * @return self
         */
        public function setBody(Component $body): self
        {
            $body->classList->addOne('card-body');
            $this->appendChild($body);
            return $this;
        }
    }

}
