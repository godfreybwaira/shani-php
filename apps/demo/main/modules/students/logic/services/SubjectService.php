<?php

/**
 * Description of SubjectRepo
 * @author coder
 *
 * Created on: May 8, 2025 at 9:14:04 AM
 */

namespace apps\demo\main\modules\students\logic\services {

    use apps\demo\main\modules\students\data\entities\SubjectEntity;
    use features\persistence\DatabaseInterface;

    final class SubjectService
    {

        private static SubjectService $object;
        private readonly DatabaseInterface $db;

        private function __construct(DatabaseInterface $database)
        {
            $this->db = $database;
        }

        public static function getObject(DatabaseInterface $database): SubjectService
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
