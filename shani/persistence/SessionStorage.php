<?php

/**
 * Description of SessionStorage
 * @author coder
 *
 * Created on: Mar 20, 2025 at 12:59:35 PM
 */

namespace shani\persistence {

    final class SessionStorage
    {

        public const TABLE_NAME = 'Session', TABLE_ID = 'sessionId';

        private array $carts = [];
        private readonly Database $db;
        private ?string $sessionId = null;

        public function __construct(string $storagePath)
        {

            $this->db = new Database('sqlite', $storagePath);
            $this->createTables();
        }

        public function start(): void
        {
            $sessName = $this->app->config->sessionName();
            $this->sessionId = $this->app->request->cookies($sessName);
            if ($this->sessionId !== null && $this->app->config->isAsync()) {
                return;
            }
            $newId = sha1(random_bytes(random_int(20, 70)));
            if ($this->sessionId !== null) {
                $this->update($newId);
            } else {
                $this->create($newId);
            }
            $cookie = (new Cookie())->setHttpOnly(true)->setName($sessName)
                    ->setValue($newId)->setSecure($this->app->request->uri->secure())
                    ->setDomain($this->app->request->uri->hostname)
                    ->setMaxAge($this->app->config->cookieMaxAge());
            $this->app->response->setCookie($cookie);
        }

        private function createTables(): void
        {
            $this->db->runQuery('PRAGMA foreign_keys=ON');

            $query = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ' TEXT PRIMARY KEY,createdAt INTEGER NOT NULL,lastActive INTEGER)';
            $this->db->runQuery($query);

            $query = 'CREATE TABLE IF NOT EXISTS ' . SessionCart::TABLE_NAME . '(' . SessionCart::TABLE_ID;
            $query .= ' INTEGER PRIMARY KEY,' . self::TABLE_ID . ' TEXT NOT NULL,cartName TEXT NOT NULL,FOREIGN KEY(';
            $query .= self::TABLE_ID . ') REFERENCES ' . self::TABLE_NAME . '(' . self::TABLE_ID . ')';
            $query .= 'ON DELETE CASCADE ON UPDATE CASCADE,UNIQUE(';
            $query .= self::TABLE_ID . ',cartName))';
            $this->db->runQuery($query);

            $query = 'CREATE TABLE IF NOT EXISTS ' . SessionCart::DATA_TABLE . '(' . SessionCart::TABLE_ID;
            $query .= ' INTEGER NOT NULL,FOREIGN KEY(' . SessionCart::TABLE_ID . ') REFERENCES ';
            $query . SessionCart::TABLE_NAME . '(' . SessionCart::TABLE_ID . ') ON DELETE CASCADE ON UPDATE CASCADE,';
            $query .= 'dataKey TEXT NOT NULL,dataValue TEXT NOT NULL,UNIQUE(' . SessionCart::TABLE_ID . ',dataKey))';
            $this->db->runQuery($query);
        }

        private function create(string $sessionId): bool
        {
            $query = 'INSERT INTO ' . self::TABLE_NAME . ' SET ' . self::TABLE_ID . ' = :id';
            $result = $this->db->runQuery($query, ['id' => $sessionId]);
            $this->sessionId = $sessionId;
            return $result > 0;
        }

        private function update(string $newId): bool
        {
            $query = 'UPDATE ' . self::TABLE_NAME . ' SET ' . self::TABLE_ID . ' = :newId ';
            $query .= 'WHERE ' . self::TABLE_ID . ' = :currentId';
            $result = $this->db->runQuery($query, ['currentId' => $this->sessionId, 'newId' => $newId]);
            $this->sessionId = $newId;
            return $result > 0;
        }

        public function clear(): bool
        {
            $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . self::TABLE_ID . ' = :id';
            $result = $this->db->runQuery($query, ['id' => $this->sessionId]);
            unset($this->carts);
            return $result > 0;
        }

        public function cart(string $name): SessionCart
        {
            $this->carts[$name] ??= $this->getCart($name);
            if (empty($this->carts[$name])) {
                $this->carts[$name] = $this->createCart($name);
            }
            return $this->carts[$name];
        }

        private function getCart(string $name): ?SessionCart
        {
            $query = 'SELECT * FROM ' . SessionCart::TABLE_NAME . ' WHERE ';
            $query .= self::TABLE_ID . ' = :sessId AND cartName = :cartName LIMIT 1';
            $result = $this->db->getResult($query, ['sessId' => $this->sessionId, 'cartName' => $name]);
            if (empty($result)) {
                return null;
            }
            return new SessionCart($this->db, $result[0]);
        }

        private function createCart(string $name): ?SessionCart
        {
            $query = 'INSERT INTO ' . SessionCart::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ', cartName)VALUES(:sessId,:cartName) ON CONFLICT(' . self::TABLE_ID;
            $query .= ', cartName) DO NOTHING';
            $this->db->runQuery($query, ['sessId' => $this->sessionId, 'cartName' => $name]);
            return $this->getCart($name);
        }
    }

}
