<?php

/**
 * Description of StudentService
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\schools\logic\services {

    use apps\demo\modules\schools\data\entities\StudentEntity;
    use features\persistence\DatabaseInterface;

    final class StudentService
    {

        private static StudentService $object;
        private readonly SubjectService $subjectService;
        private readonly DatabaseInterface $db;

        private function __construct(DatabaseInterface $database)
        {
            $this->db = $database;
            $this->subjectService = SubjectService::getObject($database);
        }

        public static function getObject(DatabaseInterface $database): StudentService
        {
            if (!isset(self::$object)) {
                self::$object = new self($database);
            }
            return self::$object;
        }

        public function getAll(): array
        {
            $students[] = new StudentEntity('Godfrey', 'Bwaira', 20);
            $students[] = new StudentEntity('Raheli', 'Bwaira', 18);
            $students[] = new StudentEntity('Bile', 'Bwaira', 18);
            $subjects = $this->subjectService->getAll();
            foreach ($students as &$student) {
                foreach ($subjects as $subject) {
                    $student->addSubject($subject);
                }
            }
            return $students;
        }

        public function getById(int $id): ?StudentEntity
        {
            $student = new StudentEntity('Godfrey', 'Bwaira', 20);
            $subjects = $this->subjectService->getAll();
            foreach ($subjects as $subject) {
                $student->addSubject($subject);
            }
            return $student;
        }

        public function save(StudentEntity $student): ?StudentEntity
        {
            return $student;
        }
    }

}
