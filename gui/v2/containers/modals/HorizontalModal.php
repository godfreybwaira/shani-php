<?php

/**
 * Description of HorizontalModal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    final class HorizontalModal extends ModalWrapper
    {

        public function __construct()
        {
            parent::__construct('modal-horizontal');
        }

        /**
         * Set modal position
         * @param bool $align Whether to align bottom or not
         * @return self
         */
        public function alignBottom(bool $align = true): self
        {
            if ($align) {
                $this->classList->addOne('modal-pos-bottom');
            } else {
                $this->classList->delete('modal-pos-bottom');
            }
            return $this;
        }
    }

}
