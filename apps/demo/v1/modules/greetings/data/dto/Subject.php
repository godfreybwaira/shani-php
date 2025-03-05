<?php

/**
 * Description of Student
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\v1\modules\greetings\data\dto {

    final class Subject implements \shani\contracts\ResponseDto
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
        public function asMap(): array
        {
            return [
                'name' => $this->name,
                'grade' => $this->grade,
                'marks' => $this->marks,
            ];
        }
    }

}
