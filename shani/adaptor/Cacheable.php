<?php

/**
 * Description of Cacheable
 * @author coder
 *
 * Created on: Mar 26, 2024 at 1:39:50 PM
 */

namespace shani\adaptor {

    interface Cacheable
    {

        public function add(string $id, array $items, $keys = null, bool $selected = true): self;

        public function replace(string $id, $items, $keys = null, bool $selected = true): self;

        public function get(string $id, $keys = null, bool $selected = true);

        public function exists(string $id, $keys = null): bool;

        public function remove(string $id, $keys, bool $selected = true): self;

        public function delete(string $id, int $maxAge = 0): self;

        public function rename(string $oldId, string $newId): self;
    }

}
