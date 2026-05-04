<?php

namespace apps\abc\modules\users\data\entities {

    final class AccountEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

