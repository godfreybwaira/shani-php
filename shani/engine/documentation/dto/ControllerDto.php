<?php

/**
 * Description of ControllerDto
 * @author coder
 *
 * Created on: Feb 22, 2025 at 1:15:09 PM
 */

namespace shani\engine\documentation\dto {

    final class ControllerDto
    {

        private readonly string $name;
        private array $methods;
        private static array $list = [];

        public function __construct(string $name)
        {
            $this->name = $name;
            if (!self::exists($name)) {
                self::$list[] = $name;
            }
        }

        public static function exists(string $name): bool
        {
            return in_array($name, self::$list);
        }

        public function addRequestMethod(RequestMethodDto $method): self
        {
            $this->methods[] = $method->dto();
            return $this;
        }

        public function dto(): array
        {
            return [
                'name' => $this->name,
                'methods' => $this->methods
            ];
        }
    }

}
