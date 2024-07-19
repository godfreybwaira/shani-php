<?php

/**
 * Form represents a component that collects user input then submitting them to
 * a server for further processing. This component has no default styles
 * @author coder
 *
 * Created on: Jul 19, 2024 at 7:02:10 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Form extends Component
    {

        private ?Component $fieldset = null;

        /**
         *
         * @param bool $fieldset A wrapper for all form elements.
         */
        public function __construct(bool $fieldset = true)
        {
            parent::__construct('form');

            if ($fieldset) {
                $this->fieldset = new Component('fieldset');
            }
        }

        public function build(): string
        {
            if ($this->fieldset !== null) {
                if ($this->hasChildren()) {
                    $this->moveChildrenTo($this->fieldset);
                    $this->appendChildren($this->fieldset);
                }
                $this->moveContentTo($this->fieldset);
            }
            return parent::build();
        }
    }

}
