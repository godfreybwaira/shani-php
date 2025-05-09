<?php

/**
 * Description of StudentEntity
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\schools\data\entities {

    final class StudentEntity
    {

        private readonly string $firstName, $lastName;
        private readonly int $age, $id;
        private array $subjects = [];
        private static int $studentId = 0;

        public function __construct(string $fname, string $lname, int $age)
        {
            $this->firstName = $fname;
            $this->lastName = $lname;
            $this->age = $age;
            $this->id = ++self::$studentId;
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function addSubject(SubjectEntity $subject): self
        {
            $this->subjects[] = $subject;
            return $this;
        }

        public function getFirstName(): string
        {
            return $this->firstName;
        }

        public function getLastName(): string
        {
            return $this->lastName;
        }

        public function getAge(): int
        {
            return $this->age;
        }

        public function getSubjects(): array
        {
            return $this->subjects;
        }
    }

}
