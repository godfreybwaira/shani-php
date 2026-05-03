<?php

namespace apps\mataadb\modules\users\data\entities {

    final class AccountEntity
    {

        public readonly string|int $id;

        public function __construct(string|int $id)
        {
            $this->id = $id;
        }
    }

}

