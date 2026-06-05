<?php

/**
 * Description of Modal
 * @author coder
 *
 * @since May 16, 2025 at 9:09:35 PM
 */

namespace gui\v2\containers\modals {

    use gui\v2\decorators\Position;

    final class Modal extends ModalWrapper
    {

        public function __construct(Position $pos = Position::CENTER)
        {
            parent::__construct('modal-type-c');
            $this->classList->addOne($pos->value);
        }
    }

}
