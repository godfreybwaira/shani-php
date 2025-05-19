<?php

/**
 * Description of VerticalModal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\DeviceSize;
    use gui\v2\Position;

    final class VerticalModal extends ModalWrapper
    {

        public function __construct()
        {
            parent::__construct('modal-v');
        }

        /**
         * Set modal position
         * @param bool $align Whether to align right or not
         * @return self
         */
        public function alignRight(bool $align = true): self
        {
            if ($align) {
                $this->classList->addOne(Position::RIGHT->value);
            } else {
                $this->classList->delete(Position::RIGHT->value);
            }
            return $this;
        }

        public function addSize(DeviceSize $device, int $size): self
        {
            $this->classList->addOne('height-' . $device->value . '-' . $size);
            return $this;
        }
    }

}
