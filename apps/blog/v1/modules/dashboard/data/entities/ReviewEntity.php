<?php

namespace apps\blog\v1\modules\dashboard\data\entities {

    final class ReviewEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

