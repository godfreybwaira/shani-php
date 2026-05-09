<?php

namespace apps\blog\v1\modules\users\data\dto {

    use apps\blog\v1\modules\users\data\entities\SampleEntity;

    final class SampleDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from SampleEntity to SampleDto type
         * @param SampleEntity $entity SampleEntity object
         * @return SampleDto
         */
        public static function entity2dto(SampleEntity $entity): SampleDto
        {
            $dto = new SampleDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from SampleDto to SampleEntity type
         * @param SampleDto $dto SampleDto object
         * @return SampleEntity
         */
        public static function dto2entity(SampleDto $dto): SampleEntity
        {
            $entity = new SampleEntity($dto->id);
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

