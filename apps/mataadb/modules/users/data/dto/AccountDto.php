<?php

namespace apps\mataadb\modules\users\data\dto {

    final class AccountDto
    {

        public readonly string|int $id;

        public function __construct(string|int $id)
        {
            $this->id = $id;
        }
    }

}

