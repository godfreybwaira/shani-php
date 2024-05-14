<?php

/**
 * Description of Lens
 * @author coder
 *
 * Created on: May 12, 2024 at 12:00:03 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Lens extends Component
    {

        private bool $wrapped = false;
        private static bool $script = false;
        private ?string $src = null;

        private const NAME = 'lens';

        public function __construct()
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
        }

        private static function wrap(self $lens): Component
        {
            $src = null;
            if (!self::$script) {
                self::$script = true;
                $src = '<script defer src="' . $lens->src . '"></script>';
            }
            $wrapper = new Component('div', $src);
            $wrapper->setProps([self::NAME . '-container']);
            return $wrapper->appendChildren($lens);
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return self::wrap($this)->build();
            }
            return parent::build();
        }
    }

}
