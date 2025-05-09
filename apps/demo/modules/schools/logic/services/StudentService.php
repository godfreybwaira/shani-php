<?php

/**
 * Description of StudentService
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\schools\logic\services {

    use apps\demo\modules\schools\data\entities\StudentEntity;
    use shani\persistence\Database;

    final class StudentService
    {

        private static StudentService $object;
        private readonly SubjectService $subjectService;
        private readonly Database $conn;

        private function __construct(Database $connection)
        {
            $this->conn = $connection;
            $this->subjectService = SubjectService::getObject($connection);
        }

        public static function getObject(Database $connection): StudentService
        {
            if (!isset(self::$object)) {
                self::$object = new self($connection);
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
