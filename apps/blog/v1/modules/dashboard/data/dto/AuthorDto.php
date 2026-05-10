<?php

namespace apps\blog\v1\modules\dashboard\data\dto {

    use apps\blog\v1\modules\dashboard\data\entities\AuthorEntity;

    final class AuthorDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from AuthorEntity to AuthorDto type
         * @param AuthorEntity $entity AuthorEntity object
         * @return AuthorDto
         */
        public static function entity2dto(AuthorEntity $entity): AuthorDto
        {
            $dto = new AuthorDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from AuthorDto to AuthorEntity type
         * @param AuthorDto $dto AuthorDto object
         * @return AuthorEntity
         */
        public static function dto2entity(AuthorDto $dto): AuthorEntity
        {
            $entity = new AuthorEntity($dto->id);
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

