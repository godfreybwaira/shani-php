<?php

namespace apps\blog\v1\modules\users\data\dto {

    use apps\blog\v1\modules\users\data\entities\AcademicsEntity;

    final class AcademicsDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from AcademicsEntity to AcademicsDto type
         * @param AcademicsEntity $entity AcademicsEntity object
         * @return AcademicsDto
         */
        public static function entity2dto(AcademicsEntity $entity): AcademicsDto
        {
            $dto = new AcademicsDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from AcademicsDto to AcademicsEntity type
         * @param AcademicsDto $dto AcademicsDto object
         * @return AcademicsEntity
         */
        public static function dto2entity(AcademicsDto $dto): AcademicsEntity
        {
            $entity = new AcademicsEntity($dto->id);
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

