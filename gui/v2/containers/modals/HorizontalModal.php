<?php

/**
 * Description of HorizontalModal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\DeviceSize;
    use gui\v2\Position;

    final class HorizontalModal extends ModalWrapper
    {

        public function __construct()
        {
            parent::__construct('modal-h');
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

        public function addSize(DeviceSize $device, int $size): self
        {
            $this->classList->addOne('width-' . $device->value . '-' . $size);
            return $this;
        }
    }

}
