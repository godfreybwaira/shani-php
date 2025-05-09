<?php

/**
 * Description of SubjectRepo
 * @author coder
 *
 * Created on: May 8, 2025 at 9:14:04 AM
 */

namespace apps\demo\modules\schools\logic\services {

    use apps\demo\modules\schools\data\entities\SubjectEntity;
    use shani\persistence\Database;

    final class SubjectService
    {

        private static SubjectService $object;
        private readonly Database $conn;

        private function __construct(Database $connection)
        {
            $this->conn = $connection;
        }

        public static function getObject(Database $connection): SubjectService
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
