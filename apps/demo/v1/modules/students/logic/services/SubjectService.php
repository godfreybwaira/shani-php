<?php

/**
 * Description of SubjectRepo
 * @author coder
 *
 * @since May 8, 2025 at 9:14:04 AM
 */

namespace apps\demo\v1\modules\students\logic\services {

    use apps\demo\v1\modules\students\data\entities\SubjectEntity;
    use features\persistence\DBInterface;

    final class SubjectService
    {

        private static SubjectService $object;
        private readonly DBInterface $db;

        private function __construct(DBInterface $database)
        {
            $this->db = $database;
        }

        public static function getObject(DBInterface $database): SubjectService
        {
            if (!isset(self::$object)) {
                self::$object = new self($database);
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
