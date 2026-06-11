<?php

/**
 * Description of SubjectRepo
 * @author coder
 *
 * @since May 8, 2025 at 9:14:04 AM
 */

namespace apps\demo\v1\modules\students\logic\services {

    use apps\demo\v1\modules\students\data\entities\SubjectEntity;
    use features\persistence\QueryInterface;

    final class SubjectService
    {

        private static SubjectService $object;
        private readonly QueryInterface $db;

        private function __construct(QueryInterface $database)
        {
            $this->db = $database;
        }

        public static function getObject(QueryInterface $database): SubjectService
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
