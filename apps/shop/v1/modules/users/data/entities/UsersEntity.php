<?php

namespace apps\shop\v1\modules\users\data\entities {

    final class UsersEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

