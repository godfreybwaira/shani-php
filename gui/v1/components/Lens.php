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

        private ?string $script;
        private bool $wrapped = false;

        private const NAME = 'lens';

        public function __construct(string $script = null)
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
            if ($script !== null) {
                $this->script = '<script defer src="' . $script . '"></script>';
            }
        }

        private static function wrap(self $lens): Component
        {
            $wrapper = new Component('div', false);
            $wrapper->setContent($lens->script);
            $wrapper->setProps([self::NAME . '-wrapper']);
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
