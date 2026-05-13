<?php

namespace apps\shop\v1\modules\products\data\dto {

    use apps\shop\v1\modules\products\data\entities\ProductsEntity;

    final class ProductsDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from ProductsEntity to ProductsDto type
         * @param ProductsEntity $entity ProductsEntity object
         * @return ProductsDto
         */
        public static function entity2dto(ProductsEntity $entity): ProductsDto
        {
            $dto = new ProductsDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from ProductsDto to ProductsEntity type
         * @param ProductsDto $dto ProductsDto object
         * @return ProductsEntity
         */
        public static function dto2entity(ProductsDto $dto): ProductsEntity
        {
            $entity = new ProductsEntity($dto->id);
            return $entity;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id
            ];
        }
    }

}

