<?php

/**
 * Description of Student
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\greetings\data\dto {

    final class Subject implements \JsonSerializable
    {

        private string $name, $grade;
        private float $marks;

        public function __construct(string $name, string $grade, float $marks)
        {
            $this->name = $name;
            $this->grade = $grade;
            $this->marks = $marks;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'name' => $this->name,
                'grade' => $this->grade,
                'marks' => $this->marks,
            ];
        }
    }

}
