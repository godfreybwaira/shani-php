<?php

/**
 * Description of SessionStorage
 * @author coder
 *
 * Created on: Mar 20, 2025 at 12:59:35 PM
 */

namespace shani\persistence {

    use lib\Cookie;
    use shani\http\App;

    final class Session
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

        public function start(App &$app): void
        {
            $sessName = $app->config->sessionName();
            $this->sessionId = $app->request->cookies($sessName);
            if ($this->sessionId !== null && $app->config->isAsync()) {
                return;
            }
            $newId = sha1(random_bytes(random_int(20, 70)));
            if ($this->sessionId !== null) {
                $this->update($newId);
            } else {
                $this->create($newId);
            }
            $cookie = (new Cookie())->setHttpOnly(true)->setName($sessName)
                    ->setValue($newId)->setSecure($app->request->uri->secure())
                    ->setDomain($app->request->uri->hostname)
                    ->setMaxAge($app->config->cookieMaxAge());
            $app->response->setCookie($cookie);
        }

        private function createTables(): void
        {
            $this->db->runQuery('PRAGMA foreign_keys=ON');

            $query = 'CREATE TABLE IF NOT EXISTS ' . self::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ' TEXT PRIMARY KEY,createdAt INTEGER NOT NULL,lastActive INTEGER)';
            $this->db->runQuery($query);

            $query = 'CREATE TABLE IF NOT EXISTS ' . SessionCart::TABLE_NAME;
            $query .= '(' . SessionCart::TABLE_ID . ' INTEGER PRIMARY KEY AUTOINCREMENT,';
            $query .= self::TABLE_ID . ' TEXT NOT NULL,cartName TEXT NOT NULL,FOREIGN KEY(';
            $query .= self::TABLE_ID . ') REFERENCES ' . self::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ')ON DELETE CASCADE ON UPDATE CASCADE,UNIQUE(' . self::TABLE_ID . ',cartName))';
            $this->db->runQuery($query);

            $query = 'CREATE TABLE IF NOT EXISTS ' . SessionCart::DATA_TABLE . '(';
            $query .= SessionCart::TABLE_ID . ' INTEGER NOT NULL,dataKey TEXT NOT NULL,';
            $query .= 'dataValue TEXT NOT NULL,FOREIGN KEY(' . SessionCart::TABLE_ID;
            $query .= ') REFERENCES ' . SessionCart::TABLE_NAME . '(' . SessionCart::TABLE_ID;
            $query .= ')ON DELETE CASCADE ON UPDATE CASCADE,UNIQUE(';
            $query .= SessionCart::TABLE_ID . ',dataKey))';
            $this->db->runQuery($query);
        }

        private function create(string $sessionId): bool
        {
            $now = time();
            $this->sessionId = $sessionId;
            $query = 'INSERT INTO ' . self::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ',createdAt,lastActive)VALUES(:id,:createdAt,:lastActive)';
            $result = $this->db->runQuery($query, ['id' => $sessionId, 'createdAt' => $now, 'lastActive' => $now]);
            return $result > 0;
        }

        private function update(string $newId): bool
        {
            $this->sessionId = $newId;
            $query = 'UPDATE ' . self::TABLE_NAME . ' SET ' . self::TABLE_ID . ' = :newId ';
            $query .= 'WHERE ' . self::TABLE_ID . ' = :currentId';
            $result = $this->db->runQuery($query, ['currentId' => $this->sessionId, 'newId' => $newId]);
            return $result > 0;
        }

        public function clear(): bool
        {
            unset($this->carts);
            $query = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE ' . self::TABLE_ID . ' = :id LIMIT 1';
            $result = $this->db->runQuery($query, ['id' => $this->sessionId]);
            return $result > 0;
        }

        public function getOne(): ?array
        {
            $query = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE ' . self::TABLE_ID . ' = :id LIMIT 1';
            $row = $this->db->getResult($query, ['id' => $this->sessionId]);
            return $row[0] ?? null;
        }

        public function cart(string $name): SessionCart
        {
            $query = 'UPDATE ' . self::TABLE_NAME . ' SET lastActive = :now WHERE ';
            $query .= self::TABLE_ID . ' = :sessId LIMIT 1';
            $this->db->runQuery($query, ['sessId' => $this->sessionId, 'now' => time()]);
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
            $row = $this->db->getResult($query, ['sessId' => $this->sessionId, 'cartName' => $name]);
            if (empty($row)) {
                return null;
            }
            return new SessionCart($this->db, $row[0]);
        }

        private function createCart(string $name): ?SessionCart
        {
            $query = 'INSERT INTO ' . SessionCart::TABLE_NAME . '(' . self::TABLE_ID;
            $query .= ', cartName)VALUES(:sessId,:cartName)ON CONFLICT(' . self::TABLE_ID;
            $query .= ', cartName)DO NOTHING';
            $this->db->runQuery($query, ['sessId' => $this->sessionId, 'cartName' => $name]);
            return $this->getCart($name);
        }
    }

}
