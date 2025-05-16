<?php

/**
 * Description of VerticalModal
 * @author coder
 *
 * Created on: May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    final class VerticalModal extends ModalWrapper
    {

        public function __construct()
        {
            parent::__construct('modal-vertical');
        }

        /**
         * Set modal position
         * @param bool $align Whether to align right or not
         * @return self
         */
        public function alignRight(bool $align = true): self
        {
            if ($align) {
                $this->classList->addOne('modal-pos-right');
            } else {
                $this->classList->delete('modal-pos-right');
            }
            return $this;
        }
    }

}
