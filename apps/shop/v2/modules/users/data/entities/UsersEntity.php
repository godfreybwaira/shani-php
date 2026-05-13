<?php

namespace apps\shop\v2\modules\users\data\entities {

    final class UsersEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

