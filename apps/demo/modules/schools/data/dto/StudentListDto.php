<?php

/**
 * Description of StudentListDto
 * @author goddy
 *
 * Created on: Oct 20, 2025 at 7:13:29 PM
 */

namespace apps\demo\modules\schools\data\dto {

    final class StudentListDto implements \JsonSerializable
    {

        private array $studentDto;

        public function __construct()
        {
            $this->studentDto = [];
        }

        public function put(StudentDto $dto): self
        {
            $this->studentDto[] = $dto;
            return $this;
        }

        public function jsonSerialize(): array
        {
            return ['students' => $this->studentDto];
        }
    }

}
