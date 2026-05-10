<?php

namespace apps\blog\v1\modules\dashboard\data\dto {

    use apps\blog\v1\modules\dashboard\data\entities\ReviewEntity;

    final class ReviewDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from ReviewEntity to ReviewDto type
         * @param ReviewEntity $entity ReviewEntity object
         * @return ReviewDto
         */
        public static function entity2dto(ReviewEntity $entity): ReviewDto
        {
            $dto = new ReviewDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from ReviewDto to ReviewEntity type
         * @param ReviewDto $dto ReviewDto object
         * @return ReviewEntity
         */
        public static function dto2entity(ReviewDto $dto): ReviewEntity
        {
            $entity = new ReviewEntity($dto->id);
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

