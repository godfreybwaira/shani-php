<?php

/**
 * Description of Cart
 *
 * @author coder
 */

namespace shani\persistence\session {

    final class Cart implements \JsonSerializable
    {

        public readonly string $name;
        private array $data = [];

        public function __construct(string $name)
        {
            $this->name = $name;
        }

        public function add(string $key, mixed $value): self
        {
            $this->data[$key] = $value;
            return $this;
        }

        public function addAll(array $rows): self
        {
            foreach ($rows as $row) {
                $key = array_key_first($row);
                $this->data[$key] = $row[$key];
            }
            return $this;
        }

        public function delete(string $key): self
        {
            unset($this->data[$key]);
            return $this;
        }

        public function deleteAll(array $keys): self
        {
            foreach ($keys as $key) {
                unset($this->data[$key]);
            }
            return $this;
        }

        public function clear(): self
        {
            $this->data = [];
            return $this;
        }

        public function has(string $key): bool
        {
            return array_key_exists($key, $this->data);
        }

        public function hasAll(array $keys): bool
        {
            foreach ($keys as $key) {
                if (!array_key_exists($key, $this->data)) {
                    return false;
                }
            }
            return true;
        }

        public function count(): int
        {
            return count($this->data);
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return $this->data;
        }

        #[\Override]
        public function __toString()
        {
            return json_encode($this);
        }

        public function get(string $key): mixed
        {
            return $this->data[$key] ?? null;
        }

        public function getAll(?array $keys = null): array
        {
            if (empty($keys)) {
                return $this->data;
            }
            $rows = [];
            foreach ($keys as $key) {
                $rows[$key] = $this->data[$key] ?? null;
            }
            return $rows;
        }

        public function where(callable $cb): array
        {
            $rows = [];
            foreach ($this->data as $key => $value) {
                if ($cb($key, $value)) {
                    $rows[$key] = $value;
                }
            }
            return $rows;
        }
    }

}