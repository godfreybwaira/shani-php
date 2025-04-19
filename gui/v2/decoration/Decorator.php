<?php

/**
 * Description of Decorator
 * @author coder
 *
 * Created on: Mar 25, 2025 at 12:07:49 PM
 */

namespace gui\v2\decoration {

    abstract class Decorator
    {

        private readonly string $className;
        protected readonly string $classId;

        public function __construct(string $decorationName)
        {
            $this->className = $decorationName;
            $this->classId = self::createId();
        }

        /**
         * Create unique ID
         * @return string
         */
        private static function createId(string $prefix = 'id'): string
        {
            return $prefix . substr(hrtime(true), 8);
        }

        /**
         * Get decoration name
         * @return string
         */
        public final function getName(): string
        {
            return $this->className;
        }

        /**
         * Get decoration unique identifier. This is usually a CSS class
         * @return string
         */
        public final function getCss(): string
        {
            return $this->classId;
        }

        /**
         * Get Component decoration (styles)
         */
        public abstract function getDecoration(): ?string;

        /**
         * Remove decoration
         */
        public abstract function remove(): self;
    }

}
