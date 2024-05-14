<?php

/**
 * Description of QBaseORM
 *
 * @author coder
 */

namespace library\schema {

    abstract class QBaseORM
    {

        private $query, $con, $join, $having, $data, $columns, $table, $joinOn;
        private $where, $limit, $offset, $order, $group, $returnResult = false, $rollup = false;

        protected function __construct(string $foreignKeySuffix = '_fk', DBase $connection = null)
        {
            $this->data = [];
            $this->foreignKey = static::$primaryKey . $foreignKeySuffix;
            $this->con = $connection ?? DBase::connect();
        }

        protected final function preview(): string
        {
            $q = ($this->columns ? 'SELECT ' . $this->columns . ' ' : null);
            $q .= $this->query . $this->join;
            $q .= ($this->where ? ' WHERE ' . $this->where : null);
            $q .= ($this->group ? ' GROUP BY ' . $this->group : null);
            $q .= ($this->rollup ? ' WITH ROLLUP' : null);
            $q .= ($this->having ? ' HAVING ' . $this->having : null);
            $q .= ($this->order && !$this->rollup ? ' ORDER BY ' . $this->order : null);
            $q .= ($this->limit ? ' LIMIT ' . $this->limit : null);
            return $q . ($this->offset ? ' OFFSET ' . $this->offset : null);
        }

        public static final function getPK(): string
        {
            return static::$primaryKey;
        }

        public static final function getTable(): string
        {
            return static::$table;
        }

        protected final function orderBy(array $columns): QBaseORM
        {
            foreach ($columns as $key => $value) {
                $this->order .= ',`' . static::$table . "`.`$key` " . $value;
            }
            $this->order = ltrim($this->order, ',');
            return $this;
        }

        protected final function columns(array $columns, bool $escape = true): QBaseORM
        {
            $this->columns = null;
            if ($escape) {
                foreach ($columns as $key => $value) {
                    if (is_int($key)) {
                        $this->columns .= ',`' . static::$table . '`.`' . $value . '`';
                    } else {
                        $this->columns .= ',`' . static::$table . '`.`' . $key . '` AS `' . $value . '`';
                    }
                }
            } else {
                foreach ($columns as $key => $value) {
                    if (is_int($key)) {
                        $this->columns .= ',' . $value;
                    } else {
                        $this->columns .= ',' . $key . ' AS `' . $value . '`';
                    }
                }
            }
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function groupBy(array $columns, bool $withRollup = false): QBaseORM
        {
            foreach ($columns as $key) {
                $this->group .= ',`' . static::$table . "`.`$key`";
            }
            $this->group = ltrim($this->group, ',');
            $this->rollup = $withRollup;
            return $this;
        }

        protected final function maximum(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', MAX(`' . static::$table . '`.`' . $column . '`) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function average(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', COALESCE(AVG(`' . static::$table . '`.`' . $column . '`),0) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function minimum(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', MIN(`' . static::$table . '`.`' . $column . '`) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function sum(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', COALESCE(SUM(`' . static::$table . '`.`' . $column . '`),0) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function variance(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', COALESCE(VAR_POP(`' . static::$table . '`.`' . $column . '`),0) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function count(string $column): QBaseORM
        {
            if ($this->columns === '*') {
                $this->columns = null;
            }
            $this->columns .= ', COUNT(`' . static::$table . '`.`' . static::$primaryKey . '`) AS `' . $column . '`';
            $this->columns = ltrim($this->columns, ',');
            return $this;
        }

        protected final function uniteWith(QBaseORM ...$models): QBaseORM
        {
            $this->query = 'FROM `' . static::$table . '`';
            $sql = '(' . $this->preview() . ')';
            foreach ($models as $model) {
                $model->query = 'FROM `' . $model::$table . '`';
                $sql .= ' UNION (' . $model->preview() . ')';
            }
            $this->reset();
            $this->query = $sql;
            return $this;
        }

        protected final function intersectWith(QBaseORM ...$models): QBaseORM
        {
            $this->columns = 'DISTINCT ' . $this->columns;
            return $this->joinWith(...$models);
        }

        protected final function getDistinct(array $columns, int $itemsPerPage = null, int $currentPage = null): QBaseORM
        {
            $this->columns($columns);
            $this->limit = $itemsPerPage ?? $this->limit;
            $this->offset = $currentPage ? $this->limit * ($currentPage - 1) : $this->offset;
            $this->columns = 'DISTINCT ' . $this->columns;
            $this->query = 'FROM `' . static::$table . '`';
            $this->returnResult = true;
            return $this;
        }

        protected final function getFirst(?int $numRows, string $column = null, int $position = 1): QBaseORM
        {
            if ($column !== null & $column !== static::$primaryKey) {
                $this->order = '`' . static::$table . '`.`' . $column . '` ASC';
            }
            $this->limit = $numRows;
            $this->offset = $position - 1;
            $this->columns = $this->columns ?? '*';
            $this->query = 'FROM `' . static::$table . '`';
            return $this;
        }

        protected final function getLast(?int $numRows, string $column = null, int $position = 1): QBaseORM
        {
            $this->order = '`' . static::$table . '`.`' . ($column ? $column : static::$primaryKey) . '` DESC';
            $this->limit = $numRows;
            $this->offset = $position - 1;
            $this->columns = $this->columns ?? '*';
            $this->query = 'FROM `' . static::$table . '`';
            return $this;
        }

        protected final function getById(int $id, array $columns = null): QBaseORM
        {
            $this->byId($id);
            if ($columns !== null) {
                $this->columns($columns);
            } else {
                $this->columns = $this->columns ?? '*';
            }
            $this->query = 'FROM `' . static::$table . '`';
            $this->returnResult = true;
            return $this;
        }

        protected final function get(array $columns = null, int $itemsPerPage = null, int $currentPage = null): QBaseORM
        {
            if ($columns !== null) {
                $this->columns($columns);
            } else {
                $this->columns = $this->columns ?? '*';
            }
            $this->limit = $itemsPerPage ?? $this->limit;
            $this->offset = $currentPage ? $this->limit * ($currentPage - 1) : $this->offset;
            $this->query = 'FROM `' . static::$table . '`';
            $this->returnResult = true;
            return $this;
        }

        protected final function set(string $variable, $value): QBaseORM
        {
            $this->con->runQuery('SET SESSION ' . $variable . ' = ' . $value);
            return $this;
        }

        protected final function insertBatch(array $data, ?array $fillable): QBaseORM
        {
            $i = 0;
            $this->reset();
            $valueList = null;
            foreach ($data as $info) {
                if ($fillable !== null) {
                    $info = array_intersect_key($info, array_flip($fillable));
                } else {
                    $fillable = array_keys($info);
                }
                $values = $cols = null;
                foreach ($info as $key => $value) {
                    $values .= ',:' . $key . $i;
                    $this->data[':' . $key . $i] = $value;
                }
                ++$i;
                $valueList .= ',(' . ltrim($values, ',') . ')';
            }
            $this->query = 'INSERT INTO `' . static::$table . '`(`' . implode('`,`', $fillable) . '`) VALUES' . ltrim($valueList, ',');
            return $this;
        }

        protected final function insert(array $data, ?array $fillable): QBaseORM
        {
            $this->reset();
            if ($fillable !== null) {
                $data = array_intersect_key($data, array_flip($fillable));
            }
            $values = $cols = null;
            foreach ($data as $key => $val) {
                $cols .= ',`' . $key . '`';
                $values .= ',:' . $key;
            }
            $this->data = $data;
            $this->query = 'INSERT INTO `' . static::$table . '`(' . ltrim($cols, ',') . ') VALUES(' . ltrim($values, ',') . ')';
            return $this;
        }

        protected final function delete(?int $id): QBaseORM
        {
            if ($id !== null) {
                $this->byId($id);
            }
            $this->query = 'DELETE FROM `' . static::$table . '` USING `' . static::$table . '`';
            return $this;
        }

        protected final function update(?int $id, array $data, ?array $updatable): QBaseORM
        {
            if ($updatable !== null) {
                $data = array_intersect_key($data, array_flip($updatable));
            }
            if ($id !== null) {
                $this->byId($id);
            }
            $str = null;
            foreach ($data as $col => $val) {
                $str .= ', `' . static::$table . '`.`' . $col . "` = :$col";
            }
            $this->data = $data;
            $this->query = 'UPDATE `' . static::$table . '`' . $this->join . ' SET ' . ltrim($str, ' ,');
            $this->join = null;
            return $this;
        }

        protected final function having(array $condition, bool $useAnd = true): QBaseORM
        {
            $cnc = $useAnd ? ' AND ' : ' OR ';
            $this->having = ($this->having ? $this->having . $cnc : null) . $this->condition($condition, $useAnd);
            return $this;
        }

        protected final function where(array $condition, bool $useAnd = true): QBaseORM
        {
            $cnc = $useAnd ? ' AND ' : ' OR ';
            $this->where = ($this->where ? $this->where . $cnc : null) . $this->condition($condition, $useAnd);
            return $this;
        }

        private function condition(array $condition, bool $useAnd)
        {
            $str = null;
            $cnc = $useAnd ? ' AND ' : ' OR ';
            foreach ($condition as $col => $val) {
                $col .= !preg_match('/[<=>][ ]*$/', $col) ? ' = ' : null;
                $key = ':' . rtrim($col, '<=> ');
                $str .= $cnc . $col . $key;
                $this->data[$key] = $val;
            }
            return ltrim($str, $cnc);
        }

        protected final function toggle(?int $id, array $data): QBaseORM
        {
            $str = null;
            if ($id !== null) {
                $this->byId($id);
            }
            foreach ($data as $col) {
                $str .= ', `' . static::$table . '`.`' . $col . '` = NOT `' . static::$table . '`.`' . $col . '`';
            }
            $this->data = $data;
            $this->query = 'UPDATE `' . static::$table . '`' . $this->join . ' SET ' . ltrim($str, ' ,');
            $this->join = null;
            return $this;
        }

        protected final function increment(?int $id, array $data): QBaseORM
        {
            $str = null;
            if ($id !== null) {
                $this->byId($id);
            }
            foreach ($data as $col => $val) {
                $str .= ', `' . static::$table . '`.`' . $col . '` = `' . static::$table . '`.`' . $col . "` + :$col";
            }
            $this->data = $data;
            $this->query = 'UPDATE `' . static::$table . '`' . $this->join . ' SET ' . ltrim($str, ' ,');
            $this->join = null;
            return $this;
        }

        protected final function whereNull(array $condition, bool $useAnd = null, $isNull = true): QBaseORM
        {
            $str = null;
            $cnc = $useAnd ? ' AND ' : ' OR ';
            $not = !$isNull ? 'NOT ' : null;
            foreach ($condition as $col) {
                $str .= $cnc . $col . ' IS' . $not . ' NULL ';
            }

            $this->where = ($this->where ? $this->where . $cnc : null) . ltrim($str, $cnc);
            return $this;
        }

        protected final function whereBetween(array $condition, bool $useAnd = true, $isNot = false): QBaseORM
        {
            $str = null;
            $cnc = $useAnd ? ' AND ' : ' OR ';
            $not = $isNot ? 'NOT ' : null;
            foreach ($condition as $key => $col) {
                $str .= $cnc . $key . $not . ' BETWEEN (:' . ($key . '0') . ' AND :' . ($key . '1)');
                $this->data[$key . '0'] = $col[0];
                $this->data[$key . '1'] = $col[1];
            }
            $this->where = ($this->where ? $this->where . $cnc : null) . ltrim($str, $cnc);
            return $this;
        }

        protected final function byId(int $id): QBaseORM
        {
            $this->where = ('`' . static::$table . '`.`' . static::$primaryKey . '` = ' . $id) . ($this->where ? ' AND ' . $this->where : null);
            return $this;
        }

        protected final function whereIn(string $column, array $possibleValues, bool $notIn = false): QBaseORM
        {
            $values = null;
            foreach ($possibleValues as $k => $val) {
                $values .= ",:w$k";
                $this->data[":w$k"] = $val;
            }
            $in = $notIn ? ' NOT' : null;
            $this->where = ('`' . $column . '`' . $in . ' IN(' . ltrim($values, ',') . ')') . ($this->where ? ' AND ' . $this->where : null);
            return $this;
        }

        protected final function joinWith(QBaseORM ...$models): QBaseORM
        {
            $thisColumn = $this->joinOn[0] ?? static::$primaryKey;
            $modelColumn = $this->joinOn[1] ?? $this->foreignKey;
            foreach ($models as $model) {
                $this->join .= ',`' . $model::$table . '`';
                $this->where .= ' AND `' . static::$table . '`.`' . $thisColumn . '` = ';
                $this->where .= '`' . $model::$table . '`.`' . $modelColumn . '`';
                $this->prepareJoin($model);
            }
            $this->where = ltrim($this->where, ' AND ');
            return $this;
        }

        protected final function joinRight(QBaseORM ...$models): QBaseORM
        {
            foreach ($models as $model) {
                $this->prepareJoin($model, 'RIGHT');
            }
            return $this;
        }

        protected final function joinRightUnmatch(QBaseORM ...$models): QBaseORM
        {
            foreach ($models as $model) {
                $this->where .= ' AND `' . static::$table . '`.`' . static::$primaryKey . '` IS NULL';
                $this->prepareJoin($model, 'RIGHT');
            }
            $this->where = ltrim($this->where, ' AND ');
            return $this;
        }

        protected final function joinLeft(QBaseORM ...$models): QBaseORM
        {
            foreach ($models as $model) {
                $this->prepareJoin($model, 'LEFT');
            }
            return $this;
        }

        protected final function joinLeftUnmatch(QBaseORM ...$models): QBaseORM
        {
            foreach ($models as $model) {
                $this->where .= ' AND `' . $model::$table . '`.`' . $model::$primaryKey . '` IS NULL';
                $this->prepareJoin($model, 'LEFT');
            }
            $this->where = ltrim($this->where, ' AND ');
            return $this;
        }

        protected final function joinCross(QBaseORM ...$models): QBaseORM
        {
            $tbl = null;
            foreach ($models as $model) {
                $tbl .= ',`' . $model::$table . '`';
                $this->prepareJoin($model);
            }
            $this->join .= ' CROSS JOIN ' . ltrim($tbl, ',');
            return $this;
        }

        private function prepareJoin(QBaseORM $model, string $type = null): void
        {
            if ($type) {
                $thisColumn = $this->joinOn[0] ?? static::$primaryKey;
                $modelColumn = $this->joinOn[1] ?? $this->foreignKey;
                $this->join .= ' ' . $type . ' JOIN `' . $model::$table . '` ON `' . static::$table . '`';
                $this->join .= '.`' . $thisColumn . '` = `' . $model::$table . '`.`' . $modelColumn . '`';
            }
            $this->where .= $model->where ? ' AND ' . $model->where : null;
            $this->having .= $model->having ? ' AND ' . $model->having : null;
            $this->group .= $model->group ? ', ' . $model->group : null;
            $this->order .= $model->order ? ', ' . $model->order : null;
        }

        protected final function joinOn(string $key, string $value = null): QBaseORM
        {
            $this->joinOn[] = $key;
            $this->joinOn[] = $value ?? $key;
            return $this;
        }

        protected final function reset(): void
        {
            $this->data = [];
            $this->query = $this->joinOn = $this->columns = $this->join = $this->where = null;
            $this->limit = $this->offset = $this->having = $this->order = $this->group = null;
        }

        protected final function run()
        {
            if ($this->returnResult) {
                $result = $this->con->getResult($this->preview(), $this->data);
            } else {
                $result = $this->con->runQuery($this->preview(), $this->data);
            }
            $this->reset();
            return $result;
        }
    }

}
