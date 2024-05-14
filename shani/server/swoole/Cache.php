<?php

/**
 * Description of Cache
 * @author coder
 *
 * Created on: Mar 9, 2024 at 12:52:53 PM
 */

namespace shani\server\swoole {

    final class Cache implements \shani\adaptor\Cacheable
    {

        private \Swoole\Table $table;

        public function __construct(int $maxRows, int $columnSize = 100)
        {
            $this->table = new \Swoole\Table($maxRows);
            $this->table->column('data', \Swoole\Table::TYPE_STRING, $columnSize);
            $this->table->column('ttl', \Swoole\Table::TYPE_INT, 10);
            $this->table->create();
        }

        public function add(string $id, array $items, $keys = null, bool $selected = true): self
        {
            $data = $this->table->get($id, 'data');
            $values = \library\Map::get($items, $keys, $selected);
            if (!empty($data)) {
                $data = unserialize($data);
                $values = array_merge(is_array($data) ? $data : [$data], is_array($values) ? $values : [$values]);
            }
            return $this->replace($id, $values);
        }

        public function exists(string $id, $keys = null): bool
        {

            if ($keys === null) {
                return $this->table->exist($id);
            }
            $data = $this->table->get($id, 'data');
            if (!empty($data)) {
                return \library\Map::has(unserialize($data), $keys);
            }
            return false;
        }

        public function get(string $id, $keys = null, bool $selected = true)
        {
            $data = $this->table->get($id, 'data');
            if (!empty($data)) {
                $data = unserialize($data);
                return is_array($data) ? \library\Map::get($data, $keys, $selected) : $data;
            }
            return null;
        }

        public function replace(string $id, $items, $keys = null, bool $selected = true): self
        {
            $data = is_array($items) ? \library\Map::get($items, $keys, $selected) : $items;
            $this->table->set($id, ['data' => serialize($data), 'ttl' => time()]);
            return $this;
        }

        public function delete(string $id, int $maxAge = 0): self
        {
            if ($maxAge > 0) {
                $ttl = $this->table->get($id, 'ttl');
                if (time() - $ttl <= $maxAge) {
                    return $this;
                }
            }
            $this->table->del($id);
            return $this;
        }

        public function remove(string $id, $keys, bool $selected = true): self
        {
            $data = $this->table->get($id, 'data');
            if (!empty($data)) {
                $data = \library\Map::get(unserialize($data), $keys, !$selected);
                return $this->replace($id, $data);
            }
            return $this;
        }

        public function rename(string $oldId, string $newId): self
        {
            $data = $this->table->get($oldId);
            if (!empty($data)) {
                $data['ttl'] = time();
                $this->table->set($newId, $data);
                $this->table->del($oldId);
            }
            return $this;
        }
    }

}
