<?php

/**
 * Description of ModalWrapper
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\Component;

    abstract class ModalWrapper extends Component
    {

        private bool $wrapped = false;
        private readonly Component $wrapper;

        protected function __construct(string $className)
        {
            parent::__construct('div');
            $this->classList->addAll(['modal', $className]);
            $this->wrapper = new Component('div');
            $this->wrapper->classList->addOne('modal-background');
            $this->wrapper->setChild($this);
            $this->setAutoclose(true);
        }

        /**
         * Whether to close the modal When clicking outside
         * @param bool $autoclose True to close, false to persist
         * @return self
         */
        public function setAutoclose(bool $autoclose): self
        {
            if ($autoclose) {
                $this->wrapper->classList->delete('no-close');
            } else {
                $this->wrapper->classList->addOne('no-close');
            }
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
