<?php

namespace apps\abc\modules\users\data\dto {

    use apps\abc\modules\users\data\entities\AccountEntity;

    final class AccountDto implements \JsonSerializable
    {

        public readonly string $id;

        public function __construct(string $id)
        {
            $this->id = $id;
        }


        /**
         * Convert a given entity from AccountEntity to AccountDto type
         * @param AccountEntity $entity AccountEntity object
         * @return AccountDto
         */
        public static function entity2dto(AccountEntity $entity): AccountDto
        {
            $dto = new AccountDto($entity->id);
            return $dto;
        }

        /**
         * Convert a given Dto from AccountDto to AccountEntity type
         * @param AccountDto $dto AccountDto object
         * @return AccountEntity
         */
        public static function dto2entity(AccountDto $dto): AccountEntity
        {
            $entity = new AccountEntity($dto->id);
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

