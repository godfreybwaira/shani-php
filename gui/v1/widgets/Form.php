<?php

/**
 * Form represents a component that collects user input then submitting them to
 * a server for further processing. This component has no default styles.
 * @author coder
 *
 * Created on: Jul 19, 2024 at 7:02:10 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;
    use lib\MediaType;

    final class Form extends Component
    {

        private ?Component $fieldset = null;

        public const ENCTYPE_JSON = MediaType::JSON;
        public const ENCTYPE_YAML = MediaType::TEXT_YAML;
        public const ENCTYPE_CSV = MediaType::TEXT_CSV;
        public const ENCTYPE_XML = MediaType::XML;
        public const ENCTYPE_MULTIPART = 'multipart/form-data';
        public const ENCTYPE_ENCODED = 'application/x-www-form-urlencoded';
        public const ENCTYPE_DEFAULT = self::ENCTYPE_MULTIPART;

        /**
         * Create a form component
         * @param string $action Form action/url (or endpoint)
         * @param string $method Form method
         * @param bool $fieldset Whether to wrap all form elements in fieldset element.
         */
        public function __construct(string $action, string $method = 'POST', bool $fieldset = true)
        {
            parent::__construct('form');
            $this->setAttribute('enctype', self::ENCTYPE_DEFAULT);
            $this->setAttribute('method', $method);
            $this->setAttribute('action', $action);
            if ($fieldset) {
                $this->fieldset = new Component('fieldset');
            }
        }

        public function build(): string
        {
            if ($this->fieldset !== null) {
                if ($this->hasChildren()) {
                    $this->moveChildrenTo($this->fieldset);
                }
                $this->appendChildren($this->fieldset);
                $this->moveContentTo($this->fieldset);
            }
            return parent::build();
        }
    }

}
