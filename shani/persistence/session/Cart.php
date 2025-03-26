<?php

/**
 * Description of Cart
 *
 * @author coder
 */

namespace shani\persistence\session {

    use lib\IterableData;

    final class Cart extends IterableData
    {

        public readonly string $name;

        public function __construct(string $name)
        {
            $this->name = $name;
            parent::__construct([]);
        }

        /**
         * Add a list of items to a cart
         * @param array $rows 2D array contain list of arrays to add
         * @return self
         */
        public function addAll(array $rows): self
        {
            foreach ($rows as $row) {
                $key = array_key_first($row);
                parent::add($key, $row[$key]);
            }
            return $this;
        }
    }

}