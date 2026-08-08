<?php

namespace apps\tinker\v1\modules\users\data\dto {

    use apps\tinker\v1\modules\users\data\entities\UsersEntity;
    use shani\http\RequestEntity;

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

        /**
         * Create a UsersDto object from a given key-value pair array.
         * @param array $data A key-value pair array
         * @return UsersDto object
         */
        public static function fromArray(array $data): UsersDto
        {
            $dto = new UsersDto($data['id']);
            return $dto;
        }

        /**
         * Create a UsersDto object from a given request object.
         * @param RequestEntity $request request object
         * @return UsersDto object
         */
        public static function fromRequest(RequestEntity $request): UsersDto
        {
            return self::fromArray($request->body()->toArray());
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

