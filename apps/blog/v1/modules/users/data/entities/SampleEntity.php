<?php

namespace apps\blog\v1\modules\users\data\entities {

    final class SampleEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

