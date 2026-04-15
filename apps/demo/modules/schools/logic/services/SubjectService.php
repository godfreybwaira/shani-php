<?php

/**
 * Description of SubjectRepo
 * @author coder
 *
 * Created on: May 8, 2025 at 9:14:04 AM
 */

namespace apps\demo\modules\schools\logic\services {

    use apps\demo\modules\schools\data\entities\SubjectEntity;
    use features\persistence\DatabaseConnection;

    final class SubjectService
    {

        private static SubjectService $object;
        private readonly DatabaseConnection $conn;

        private function __construct(DatabaseConnection $connection)
        {
            $this->conn = $connection;
        }

        public static function getObject(DatabaseConnection $connection): SubjectService
        {
            if (!isset(self::$object)) {
                self::$object = new self($connection);
            }
            return self::$object;
        }

        public function getAll(): array
        {
            $subjects[] = new SubjectEntity('English');
            $subjects[] = new SubjectEntity('Kiswahili');
            $subjects[] = new SubjectEntity('Maths');
            return $subjects;
        }

        public function getById(int $id): ?SubjectEntity
        {
            return new SubjectEntity('English');
        }
    }

}
