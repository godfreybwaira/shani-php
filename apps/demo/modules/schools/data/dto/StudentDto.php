<?php

/**
 * Description of StudentDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\schools\data\dto {

    use apps\demo\modules\schools\data\entities\StudentEntity;

    final class StudentDto implements \JsonSerializable
    {

        private readonly string $firstName, $lastName;
        private readonly int $age, $id;
        private array $subjects = [];

        public function __construct(int $id, string $fname, string $lname, int $age)
        {
            $this->firstName = $fname;
            $this->lastName = $lname;
            $this->age = $age;
            $this->id = $id;
        }

        public function addSubject(SubjectDto $subject): self
        {
            $this->subjects[] = $subject;
            return $this;
        }

        public function getSubjects(): array
        {
            return $this->subjects;
        }

        public static function toDto(StudentEntity $student): StudentDto
        {
            $dto = new StudentDto($student->getId(), $student->getFirstName(), $student->getLastName(), $student->getAge());
            $subjects = $student->getSubjects();
            foreach ($subjects as $subject) {
                $dto->addSubject(SubjectDto::toDto($subject));
            }
            return $dto;
        }

        public static function toEntity(StudentDto $dto): StudentEntity
        {
            $student = new StudentEntity($dto->firstName, $dto->lastName, $dto->age);
            $subjects = $dto->getSubjects();
            foreach ($subjects as $dto) {
                $student->addSubject(SubjectDto::toEntity($dto));
            }
            return $student;
        }

        public static function fromArray(array $data): StudentDto
        {
            $dto = new StudentDto($data['id'], $data['firstName'], $data['lastName'], $data['age']);
            foreach ($data['subjects'] as $subject) {
                $subjectDto = SubjectDto::fromArray($subject);
                $dto->addSubject($subjectDto);
            }
            return $dto;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'age' => $this->age,
                'subjects' => $this->subjects
            ];
        }
    }

}
