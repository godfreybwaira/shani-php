<?php

/**
 * Description of Cart
 *
 * @author coder
 */

namespace shani\persistence\session {

    use lib\map\MutableMap;

    final class Cart extends MutableMap
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
                parent::addOne($key, $row[$key]);
            }
            return $this;
        }
    }

}