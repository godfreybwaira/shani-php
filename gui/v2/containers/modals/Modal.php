<?php

/**
 * Description of Modal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\decoration\Position;
    use gui\v2\DeviceSize;

    final class Modal extends ModalWrapper
    {

        public function __construct(Position $pos = Position::CENTER)
        {
            parent::__construct('modal-center');
            $this->classList->addOne($pos->value);
        }

        public function addSize(DeviceSize $device, int $size): self
        {
            $this->classList->addOne('width-' . $device->value . '-' . $size);
            $this->classList->addOne('height-' . $device->value . '-' . $size);
            return $this;
        }
    }

}
