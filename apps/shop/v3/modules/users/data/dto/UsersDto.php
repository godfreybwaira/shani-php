<?php

namespace apps\shop\v3\modules\users\data\dto {

    use apps\shop\v3\modules\users\data\entities\UsersEntity;

    final class UsersDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from UsersEntity to UsersDto type
         * @param UsersEntity $entity UsersEntity object
         * @return UsersDto
         */
        public static function entity2dto(UsersEntity $entity): UsersDto
        {
            $dto = new UsersDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from UsersDto to UsersEntity type
         * @param UsersDto $dto UsersDto object
         * @return UsersEntity
         */
        public static function dto2entity(UsersDto $dto): UsersEntity
        {
            $entity = new UsersEntity($dto->id);
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

