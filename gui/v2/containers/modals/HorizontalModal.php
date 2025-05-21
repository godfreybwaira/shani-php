<?php

/**
 * Description of HorizontalModal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\decorators\Position;
    use gui\v2\props\DeviceSize;

    final class HorizontalModal extends ModalWrapper
    {

        public function __construct()
        {
            parent::__construct('modal-type-h');
        }

        /**
         * Set modal position
         * @param bool $align Whether to align bottom or not
         * @return self
         */
        public function alignBottom(bool $align = true): self
        {
            if ($align) {
                $this->classList->addOne(Position::BOTTOM->value);
            } else {
                $this->classList->delete(Position::BOTTOM->value);
            }
            return $this;
        }

        public function addHeight(DeviceSize $device, int $height): self
        {
            return $this->addDimension($device, null, $height);
        }
    }

}
