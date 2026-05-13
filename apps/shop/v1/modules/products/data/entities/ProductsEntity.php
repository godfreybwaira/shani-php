<?php

namespace apps\shop\v1\modules\products\data\entities {

    final class ProductsEntity
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }
    }

}

