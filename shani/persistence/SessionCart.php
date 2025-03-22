<?php

/**
 * Description of SessionStorage
 *
 * @author coder
 */

namespace shani\persistence {

    final class SessionCart
    {

        private readonly Database $db;
        private readonly string $ownerId, $id;

        public const TABLE_NAME = 'Cart', TABLE_ID = 'cartId', DATA_TABLE = 'CartData';

        public function __construct(Database &$db, array &$cart)
        {
            $this->db = $db;
            $this->id = $cart[self::TABLE_ID];
            $this->ownerId = $cart[Session::TABLE_ID];
        }

        public function destroy(): bool
        {
            $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . Session::TABLE_ID;
            $query .= '=:sessId AND ' . self::TABLE_ID . '=:id LIMIT 1';
            $result = $this->db->runQuery($query, ['sessId' => $this->ownerId, 'id' => $this->id]);
            return $result > 0;
        }

        public function count(): int
        {
            $query = 'SELECT COUNT(*) AS total FROM ' . self::TABLE_NAME . ' A INNER JOIN ';
            $query .= self::DATA_TABLE . ' B USING(' . self::TABLE_ID . ')WHERE A.';
            $query .= self::TABLE_ID . '=:id AND ' . Session::TABLE_ID . '=:sessId';
            $result = $this->db->getResult($query, ['sessId' => $this->ownerId, 'id' => $this->id]);
            return $result[0]['total'] ?? 0;
        }

        public function clear(): self
        {
            $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . self::TABLE_ID;
            $query .= '=:id AND ' . Session::TABLE_ID . '=:sessId';
            $this->db->runQuery($query, ['sessId' => $this->ownerId, 'id' => $this->id]);
            return $this;
        }

        public function delete(string $key): self
        {
            $query = 'DELETE FROM ' . self::DATA_TABLE . ' WHERE ' . self::TABLE_ID;
            $query .= '=:id AND dataKey=:name';
            $this->db->runQuery($query, ['id' => $this->id, 'name' => $key]);
            return $this;
        }

        public function has(string $key): bool
        {
            $query = 'SELECT COUNT(*) AS total FROM ' . self::TABLE_NAME . ' A INNER JOIN ';
            $query .= self::DATA_TABLE . ' B USING(' . self::TABLE_ID . ') WHERE ';
            $query .= 'A.' . self::TABLE_ID . '=:id AND ' . Session::TABLE_ID;
            $query .= '=:sessId AND dataKey=:name';
            $results = $this->db->getResult($query, [
                'sessId' => $this->ownerId, 'id' => $this->id, 'name' => $key
            ]);
            return !empty($results[0]['total']);
        }

        public function hasAll(array $keys): bool
        {
            $keylist = null;
            $data = [];
            foreach ($keys as $value) {
                $data[$value] = $value;
                $keylist .= ',:' . $value;
            }
            $query = 'SELECT COUNT(*) AS total FROM ' . self::TABLE_NAME . ' A INNER JOIN ';
            $query .= self::DATA_TABLE . ' B USING(' . self::TABLE_ID . ') WHERE ';
            $query .= 'A.' . self::TABLE_ID . '=:id AND ' . Session::TABLE_ID . '=:sessId';
            $query .= ' AND dataKey IN(' . substr($keylist, 1) . ')';
            $results = $this->db->getResult($query, [
                'sessId' => $this->ownerId, 'id' => $this->id, ...$data
            ]);
            return !empty($results[0]['total']) && $results[0]['total'] === count($keys);
        }

        public function get(string $key): mixed
        {
            $query = 'SELECT B.dataValue FROM ' . self::TABLE_NAME . ' A INNER JOIN ';
            $query .= self::DATA_TABLE . ' B USING(' . self::TABLE_ID . ') WHERE A.';
            $query .= self::TABLE_ID . '=:id AND ' . Session::TABLE_ID;
            $query .= '=:sessId AND dataKey=:name';
            $results = $this->db->getResult($query, [
                'sessId' => $this->ownerId, 'id' => $this->id, 'name' => $key
            ]);
            return $results[0]['dataValue'] ?? null;
        }

        public function getAll(?array $keys = null): array
        {
            $query = 'SELECT B.dataKey, B.dataValue FROM ' . self::TABLE_NAME;
            $query .= ' A INNER JOIN ' . self::DATA_TABLE . ' B USING(' . self::TABLE_ID;
            $query .= ') WHERE A.' . self::TABLE_ID . '=:id AND ' . Session::TABLE_ID . '=:sessId';
            $params = ['sessId' => $this->ownerId, 'id' => $this->id];
            if (!empty($keys)) {
                $data = [];
                $keylist = null;
                foreach ($keys as $value) {
                    $data[$value] = $value;
                    $keylist .= ',:' . $value;
                }
                $query .= ' AND dataKey IN(' . substr($keylist, 1) . ')';
                $params = array_merge($params, $data);
            }
            $results = $this->db->getResult($query, $params);
            $rows = [];
            foreach ($results as $row) {
                $rows[$row['dataKey']] = $row['dataValue'];
            }
            return $rows;
        }

        public function deleteAll(array $keys): self
        {
            $keylist = null;
            $data = [];
            foreach ($keys as $value) {
                $data[$value] = $value;
                $keylist .= ',:' . $value;
            }
            $query = 'DELETE FROM ' . self::DATA_TABLE . ' WHERE ' . self::TABLE_ID;
            $query .= '=:id AND dataKey IN(' . substr($keylist, 1) . ')';
            $this->db->runQuery($query, ['id' => $this->id, ... $data]);
            return $this;
        }

        public function add(string $key, mixed $value): self
        {
            $query = 'INSERT INTO ' . self::DATA_TABLE . '(' . self::TABLE_ID . ',dataKey,';
            $query .= 'dataValue)VALUES(:id,:name,:value)ON CONFLICT(' . self::TABLE_ID;
            $query .= ',dataKey)DO UPDATE SET dataValue = EXCLUDED.dataValue';
            $this->db->runQuery($query, ['id' => $this->id, 'name' => $key, 'value' => $value]);
            return $this;
        }

        public function addAll(array $rows): self
        {
            $keylist = null;
            $data = [];
            foreach ($rows as $key => $row) {
                $keylist .= ",(:id,:name$key,:value$key)";
                $data["name$key"] = array_key_first($row);
                $data["value$key"] = $row[$data["name$key"]];
            }

            $query = 'INSERT INTO ' . self::DATA_TABLE . '(' . self::TABLE_ID;
            $query .= ',dataKey,dataValue)VALUES' . substr($keylist, 1);
            $query .= 'ON CONFLICT(' . self::TABLE_ID . ',dataKey)';
            $query .= 'DO UPDATE SET dataValue = EXCLUDED.dataValue';
            $this->db->runQuery($query, ['id' => $this->id, ...$data]);
            return $this;
        }

        public function where(callable $cb): array
        {
            $data = [];
            $rows = $this->getAll();
            foreach ($rows as $key => $value) {
                if ($cb($key, $value)) {
                    $data[$key] = $value;
                }
            }
            return $data;
        }
    }

}