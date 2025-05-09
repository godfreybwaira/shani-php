<?php

/**
 * Description of SubjectDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\schools\data\dto {

    use apps\demo\modules\schools\data\entities\SubjectEntity;

    final class SubjectDto implements \JsonSerializable
    {

        private readonly string $name;
        private readonly int $id;

        public function __construct(int $id, string $name)
        {
            $this->name = $name;
            $this->id = $id;
        }

        public static function toDto(SubjectEntity $subject): SubjectDto
        {
            return new SubjectDto($subject->getId(), $subject->getName());
        }

        public static function toEntity(SubjectDto $dto): SubjectEntity
        {
            return new SubjectEntity($dto->id, $dto->name);
        }

        public static function fromArray(array $data): SubjectDto
        {
            return new SubjectDto($data['id'], $data['name']);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'name' => $this->name
            ];
        }
    }

}
