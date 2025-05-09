<?php

/**
 * Description of SubjectEntity
 * @author coder
 *
 * Created on: Feb 22, 2025 at 10:29:03 AM
 */

namespace apps\demo\modules\schools\data\entities {

    final class SubjectEntity
    {

        private readonly string $name;
        private readonly int $id;
        private static int $subjectId = 0;

        public function __construct(string $name)
        {
            $this->name = $name;
            $this->id = ++self::$subjectId;
        }

        public function getId(): int
        {
            return $this->id;
        }

        public function getName(): string
        {
            return $this->name;
        }
    }

}
