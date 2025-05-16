<?php

/**
 * Description of ModalWrapper
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\Component;
    use gui\v2\DeviceSize;

    abstract class ModalWrapper extends Component
    {

        private bool $wrapped = false;
        private readonly string $className;
        private readonly Component $wrapper;

        protected function __construct(string $className)
        {
            parent::__construct('div');
            $this->className = $className;
            $this->classList->addAll(['modal', $className]);
            $this->wrapper = new Component('div');
            $this->wrapper->classList->addOne('modal-background');
            $this->wrapper->setChild($this);
        }

        /**
         * Set different size based on the device width
         * @param DeviceSize $device Device size
         * @param int $size Size from 1 to 5
         * @return self
         */
        public function addSize(DeviceSize $device, int $size): self
        {
            $this->classList->addOne($this->className . '-' . $device->value . '-' . $size);
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return $this->wrapper->build();
            }
            return parent::build();
        }
    }

}
