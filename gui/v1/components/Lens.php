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

        private static ?string $script = null;
        private bool $wrapped = false, $scriptSent = false;

        private const NAME = 'lens';

        public function __construct(?string $script = null)
        {
            if ($script !== null) {
                self::$script = $script;
            } elseif (self::$script === null) {
                throw new \RuntimeException('No script source provided');
            }
            parent::__construct('div');
            $this->setProps([self::NAME]);
        }

        private static function wrap(self $lens): Component
        {
            $src = null;
            if (!self::$scriptSent) {
                self::$scriptSent = true;
                $src = '<script defer src="' . self::$script . '"></script>';
            }
            $wrapper = new Component('div', $src, false);
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
