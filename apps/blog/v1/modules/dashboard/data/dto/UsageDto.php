<?php

namespace apps\blog\v1\modules\dashboard\data\dto {

    use apps\blog\v1\modules\dashboard\data\entities\UsageEntity;

    final class UsageDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from UsageEntity to UsageDto type
         * @param UsageEntity $entity UsageEntity object
         * @return UsageDto
         */
        public static function entity2dto(UsageEntity $entity): UsageDto
        {
            $dto = new UsageDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from UsageDto to UsageEntity type
         * @param UsageDto $dto UsageDto object
         * @return UsageEntity
         */
        public static function dto2entity(UsageDto $dto): UsageEntity
        {
            $entity = new UsageEntity($dto->id);
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

