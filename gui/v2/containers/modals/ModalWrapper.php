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

        private readonly Component $wrapper;
        private bool $opened = false;

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
        public function setAutoclose(bool $autoclose = true): self
        {
            if ($autoclose) {
                $this->wrapper->attribute->addOne('ui-close', '#' . $this->wrapper->getId());
            } else {
                $this->wrapper->attribute->delete('ui-close');
            }
            return $this;
        }

        public function open(): string
        {
            if (!$this->opened) {
                $this->opened = true;
                return $this->wrapper->open();
            }
            return parent::open();
        }

        public function close(): string
        {
            if ($this->opened) {
                $this->opened = false;
                return $this->wrapper->close();
            }
            return parent::close();
        }
    }

}
