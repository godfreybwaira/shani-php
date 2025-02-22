<?php

/**
 * Description of Student
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\v1\modules\greetings\data\dto {

    final class Subject
    {

        private string $name, $grade;
        private float $marks;

        public function __construct(string $name, string $grade, float $marks)
        {
            $this->name = $name;
            $this->grade = $grade;
            $this->marks = $marks;
        }

        public function dto(): array
        {
            return [
                'name' => $this->name,
                'grade' => $this->grade,
                'marks' => $this->marks,
            ];
        }
    }

}
