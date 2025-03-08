<?php

/**
 * Description of Student
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\greetings\data\dto {

    final class Student implements \shani\contracts\DataDto
    {

        private string $firstName, $lastName;
        private int $age;
        private array $subjects;

        public function __construct(string $fname, string $lname, int $age)
        {
            $this->firstName = $fname;
            $this->lastName = $lname;
            $this->age = $age;
        }

        public function addSubject(Subject $subject): self
        {
            $this->subjects[] = $subject->asMap();
            return $this;
        }

        #[\Override]
        public function asMap(): array
        {
            return [
                'firstName' => $this->firstName,
                'lastName' => $this->lastName,
                'age' => $this->age,
                'subjects' => $this->subjects
            ];
        }
    }

}
